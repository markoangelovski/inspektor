import React from "react";
import ReactJson from "@microlink/react-json-view";

export default function JsonViewer({ data }) {
    return (
        <ReactJson
            src={data}
            name={false}
            theme="monokai"
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
