/**
 * Converts a style Object to it's inline style string representation.
 * @param {Object} style
 * @returns {string}
 */
export function styleObjectToString(style)
{
    let styles = [];

    Object.keys(style).forEach(function(key)
    {
        styles.push(`${key}:${style[key]}`);
    });

    return styles.join(";");
};
