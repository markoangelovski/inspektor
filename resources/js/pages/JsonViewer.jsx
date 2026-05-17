import React, { useState, useEffect } from "react";
import ReactJson from "@microlink/react-json-view";

function useDarkMode() {
    const [isDark, setIsDark] = useState(() =>
        document.documentElement.classList.contains("dark")
    );

    useEffect(() => {
        const observer = new MutationObserver(() => {
            setIsDark(document.documentElement.classList.contains("dark"));
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
        return () => observer.disconnect();
    }, []);

    return isDark;
}

export default function JsonViewer({ data }) {
    const isDark = useDarkMode();

    return (
        <ReactJson
            src={data}
            name={false}
            theme={isDark ? "monokai" : "rjv-default"}
            collapsed={2}
            displayDataTypes={false}
            displayObjectSize={false}
            enableClipboard={false}
            style={{
                backgroundColor: "transparent",
                fontSize: "12px",
                fontFamily: "monospace",
            }}
        />
    );
}
