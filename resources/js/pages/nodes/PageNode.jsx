import React from "react";
import { Handle, Position } from "reactflow";
import {
    HomeIcon,
    DocumentTextIcon,
    ChevronRightIcon,
    EyeIcon,
} from "@heroicons/react/24/outline";

export default function PageNode({ data }) {
    const {
        label,
        page,
        isExpanded,
        onExpand,
        onViewContent,
        isSelected,
        isHomepage,
        hasChildren,
    } = data;

    return (
        <div
            className={`
        min-w-[220px] rounded-lg border-2 px-4 py-3
        bg-white dark:bg-zinc-900
        transition-shadow
        ${
            isSelected
                ? "border-blue-500 shadow-lg shadow-blue-500/20"
                : "border-gray-300 dark:border-zinc-700"
        }
      `}
        >
            {/* Incoming / outgoing handles */}
            {!isHomepage && <Handle type="target" position={Position.Top} />}
            {hasChildren && <Handle type="source" position={Position.Bottom} />}

            {/* Header */}
            <div className="flex items-center gap-2 mb-3">
                {isHomepage ? (
                    <HomeIcon className="h-4 w-4 text-blue-500" />
                ) : (
                    <DocumentTextIcon className="h-4 w-4 text-gray-400 dark:text-zinc-400" />
                )}

                <span
                    className={`
            text-sm font-medium truncate
            ${
                isHomepage
                    ? "text-blue-600 dark:text-blue-400"
                    : "text-gray-900 dark:text-zinc-100"
            }
          `}
                    title={label}
                >
                    {label}
                </span>
            </div>

            {/* Actions */}
            <div className="flex gap-2">
                {hasChildren && (
                    <button
                        type="button"
                        onClick={() => onExpand(page.path)}
                        className="flex items-center justify-center gap-1 rounded-md px-2 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-zinc-300 transition-colors cursor-pointer"
                    >
                        <ChevronRightIcon
                            className={`h-3 w-3 transition-transform ${
                                isExpanded ? "rotate-90" : ""
                            }`}
                        />
                        {/* {isExpanded ? 'Collapse' : 'View sub-pages'} */}
                    </button>
                )}

                <button
                    type="button"
                    onClick={() => onViewContent(page.id)}
                    className="flex items-center justify-center gap-1 rounded-md px-2 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white transition-colors cursor-pointer"
                    title="View content"
                >
                    <EyeIcon className="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    );
}
