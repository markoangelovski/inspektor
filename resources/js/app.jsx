import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import PagesFlow from "./pages/PagesFlow";
import "reactflow/dist/style.css";

const mountedRoots = new WeakMap();

function mountPagesFlow(pages, selectedPageId = null) {
    const el = document.getElementById("pages-flow-root");
    if (!el) return;

    let root = mountedRoots.get(el);
    if (!root) {
        root = createRoot(el);
        mountedRoots.set(el, root);
    }

    // Pass the selectedPageId through to the component
    root.render(<PagesFlow pages={pages} selectedPageId={selectedPageId} />);
}

// Handle initial page load and wire:navigate events
["DOMContentLoaded", "livewire:navigated"].forEach((eventName) => {
    document.addEventListener(eventName, () => {
        const el = document.getElementById("pages-flow-root");
        if (!el) return;

        const initialPages = el.dataset.pages
            ? JSON.parse(el.dataset.pages)
            : [];
        if (initialPages.length) mountPagesFlow(initialPages);
    });
});

// React Flow update on Livewire events: pagination, search, perPage, selecting a page in table
document.addEventListener("pages-updated", (event) =>
    mountPagesFlow(event.detail[0].pages, event.detail[0].selectedPageId)
);
