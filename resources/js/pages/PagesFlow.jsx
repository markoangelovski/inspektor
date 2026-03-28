import React, { useEffect, useState, useMemo, useCallback } from "react";
import ReactFlow, {
    Background,
    Controls,
    Position,
    useReactFlow,
    ReactFlowProvider,
} from "reactflow";
import "reactflow/dist/style.css";

import PageNode from "./nodes/PageNode";

/**
 * Layout constants
 */
const NODE_WIDTH = 240;
const NODE_HEIGHT = 96;
const ROW_GAP = 80;
const COLUMN_GAP = 40;
const ROOT_GAP = 120;

/**
 * Helpers
 */
function formatLabel(path) {
    if (path === "/") return "Homepage";

    const parts = path.split("/").filter(Boolean);
    const lastPart = parts[parts.length - 1];

    return lastPart
        .split("-")
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(" ");
}

function buildChildrenMap(pages) {
    const map = new Map();

    pages.forEach((page) => {
        if (!page.parent_path) return;

        if (!map.has(page.parent_path)) {
            map.set(page.parent_path, []);
        }

        map.get(page.parent_path).push(page);
    });

    return map;
}

function getAllExpandableNodeIds(pages) {
    const parents = new Set();
    pages.forEach((p) => {
        if (p.parent_path) {
            parents.add(p.parent_path);
        }
    });

    return new Set(pages.filter((p) => parents.has(p.path)).map((p) => p.path));
}

/**
 * Precompute subtree widths so layout is stable
 */
function computeSubtreeWidths(pages) {
    const childrenMap = buildChildrenMap(pages);
    const widths = new Map();

    function compute(path) {
        const children = childrenMap.get(path) || [];

        if (children.length === 0) {
            widths.set(path, NODE_WIDTH);
            return NODE_WIDTH;
        }

        let total = 0;
        children.forEach((child) => {
            total += compute(child.path);
        });

        total += COLUMN_GAP * (children.length - 1);
        const finalWidth = Math.max(total, NODE_WIDTH);

        widths.set(path, finalWidth);
        return finalWidth;
    }

    // compute for ALL pages to support orphans
    pages.forEach((p) => compute(p.path));

    return widths;
}

/**
 * Build ALL trees (connected + orphan)
 */
function buildTrees({
    pages,
    expandedNodes,
    selectedPageId,
    onExpand,
    onViewContent,
    subtreeWidths,
}) {
    const childrenMap = buildChildrenMap(pages);
    const pagesByPath = new Map(pages.map((p) => [p.path, p]));

    const nodes = [];
    const edges = [];

    // Roots = no parent OR missing parent
    const roots = pages.filter(
        (p) => !p.parent_path || !pagesByPath.has(p.parent_path)
    );

    let currentX = 0;

    function walk(page, depth, xStart) {
        const children = childrenMap.get(page.path) || [];
        const subtreeWidth = subtreeWidths.get(page.path) ?? NODE_WIDTH;

        const x = xStart + subtreeWidth / 2 - NODE_WIDTH / 2;
        const y = depth * (NODE_HEIGHT + ROW_GAP);

        nodes.push({
            id: page.id,
            type: "page",
            position: { x, y },
            sourcePosition: Position.Bottom,
            targetPosition: Position.Top,
            data: {
                label: formatLabel(page.path),
                page,
                hasChildren: children.length > 0,
                isExpanded: expandedNodes.has(page.path),
                isSelected: page.id === selectedPageId,
                isHomepage: page.path === "/",
                onExpand,
                onViewContent,
            },
        });

        // Edge only if parent exists
        if (page.parent_path && pagesByPath.has(page.parent_path)) {
            const parent = pagesByPath.get(page.parent_path);
            edges.push({
                id: `edge-${parent.id}-${page.id}`,
                source: parent.id,
                target: page.id,
                type: "smoothstep",
            });
        }

        if (!expandedNodes.has(page.path)) return;

        let childX = xStart;
        children.forEach((child) => {
            const childWidth = subtreeWidths.get(child.path) ?? NODE_WIDTH;
            walk(child, depth + 1, childX);
            childX += childWidth + COLUMN_GAP;
        });
    }

    roots.forEach((root) => {
        const width = subtreeWidths.get(root.path) ?? NODE_WIDTH;
        walk(root, 0, currentX);
        currentX += width + ROOT_GAP;
    });

    return { nodes, edges };
}

/**
 * Main component
 */
function PagesFlowContent({ pages, selectedPageId = null }) {
    // Rename internal logic
    const { setCenter } = useReactFlow();
    const [nodes, setNodes] = useState([]);
    const [edges, setEdges] = useState([]);

    // Expand everything by default
    const [expandedNodes, setExpandedNodes] = useState(
        getAllExpandableNodeIds(pages)
    );

    const nodeTypes = useMemo(() => ({ page: PageNode }), []);

    const onExpand = useCallback((path) => {
        setExpandedNodes((prev) => {
            const next = new Set(prev);
            next.has(path) ? next.delete(path) : next.add(path);
            return next;
        });
    }, []);

    const onViewContent = useCallback((pageId) => {
        window.dispatchEvent(
            new CustomEvent("pages:view-content", {
                detail: { pageId },
            })
        );
    }, []);

    const subtreeWidths = useMemo(() => computeSubtreeWidths(pages), [pages]);

    useEffect(() => {
        // When changing the "pages" on the fly, set all expandable nodes as expanded
        // Happens on pagination clicks, changing perPage and search term
        setExpandedNodes(getAllExpandableNodeIds(pages));
    }, [pages]);

    useEffect(() => {
        const { nodes, edges } = buildTrees({
            pages,
            expandedNodes,
            selectedPageId,
            onExpand,
            onViewContent,
            subtreeWidths,
        });

        setNodes(nodes);
        setEdges(edges);

        // Center the selected node
        if (selectedPageId) {
            const selectedNode = nodes.find((n) => n.id === selectedPageId);
            if (selectedNode) {
                setCenter(
                    selectedNode.position.x + 120,
                    selectedNode.position.y + 50,
                    {
                        zoom: 1.2,
                        duration: 800,
                    }
                );
            }
        }
    }, [pages, expandedNodes, subtreeWidths, onExpand, onViewContent]);

    // Update onNodeClick to sync back to Livewire
    const onNodeClick = useCallback((event, node) => {
        window.dispatchEvent(
            new CustomEvent("pages:page-selected", {
                detail: { pageId: node.id },
            })
        );
    }, []);

    return (
        <ReactFlow
            nodes={nodes}
            edges={edges}
            onNodeClick={onNodeClick}
            nodeTypes={nodeTypes}
            fitView
            nodesDraggable={false}
            nodesConnectable={false}
            elementsSelectable
            panOnDrag
            zoomOnScroll
        >
            <Background />
            <Controls />
        </ReactFlow>
    );
}

// React Flow hooks require the component to be wrapped in a Provider
function PagesFlow(props) {
    return (
        <ReactFlowProvider>
            <PagesFlowContent {...props} />
        </ReactFlowProvider>
    );
}

export default React.memo(PagesFlow);
