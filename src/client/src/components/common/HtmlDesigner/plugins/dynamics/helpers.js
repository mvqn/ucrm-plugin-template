

String.prototype.minify = function()
{
    return this
        .trim()
        .replace(/>[\n\t ]+/g, ">")
        .replace(/[\n\t ]+</g, "<")
        .replace(/\s+/g, " ");
};




// noinspection ES6UnusedImports
import { parse, stringify } from "css";

/**
 *
 * @param {string} css
 * @returns {string}
 */
export function inlineFromCSS(css)
{
    const stripped = css.replace(/<\/?style>/g, "").minify();
    const styles = parse(stripped);

    let rules = [];

    if(styles.type === "stylesheet" && styles.stylesheet && styles.stylesheet.parsingErrors.length === 0)
    {
        styles.stylesheet.rules.forEach(function(rule)
        {
            rule.selectors.forEach(function(selector)
            {
                if(selector.startsWith(".dynamic-field"))
                {
                    rule.declarations.forEach(function(declaration)
                    {
                        rules.push(`${declaration.property}:${declaration.value};`)
                    });
                }
            });
        });
    }

    return rules.join(" ");
}


/**
 * Flatten a deep object into a one level object with it’s path as key
 * @param  {object} object      The object to be flattened.
 * @param  {string} delimiter   The delimiter to use.
 * @return {object}             The resulting flat object.
 */
export function flatten(object, delimiter = '.') {
    return Object.assign({}, ...function _flatten(child, path = []) {
        return [].concat(...Object.keys(child).map(key => typeof child[key] === 'object'
            ? _flatten(child[key], path.concat([key]))
            : ({ [path.concat([key]).join(delimiter)] : child[key] })
        ));
    }(object));
}


export function resolve(path, obj) {
    return path.split('.').reduce(function(prev, curr) {
        return prev ? prev[curr] : null
    }, obj || self)
}

/*
export function groupBy(key, array)
{
    array.reduce((objectsByKeyValue, obj) => {
        const value = obj[key];
        objectsByKeyValue[value] = (objectsByKeyValue[value] || []).concat(obj);
        return objectsByKeyValue;
    }, {});
}
*/


Array.prototype.groupBy = function(prop) {
    return this.reduce(function(groups, item) {
        const val = item[prop]
        groups[val] = groups[val] || []
        groups[val].push(item)
        return groups
    }, {})
}
