export function getTextContent(children) {
  const text = collectText(children);
  return text.length > 0 ? text : null;
}

function collectText(children) {
  if (children == null || typeof children === "boolean") {
    return "";
  }
  if (typeof children === "string" || typeof children === "number") {
    return String(children);
  }
  if (Array.isArray(children)) {
    return children.map(collectText).join("");
  }
  if (typeof children === "object" && "props" in children) {
    const props = children.props;
    return collectText(props?.children ?? "");
  }
  return "";
}

export function lerp(min, max, t) {
  return min + (max - min) * t;
}

export function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}
