/**
 * Returns a depply cloned object without reference.
 * Copied from Vue MultiSelect and Vuex.
 * @type {Object}
 */
export function deepClone(obj) {
  if (Array.isArray(obj)) {
    return obj.map(deepClone)
  } else if (obj && typeof obj === 'object') {
    var cloned = {}
    var keys = Object.keys(obj)
    for (var i = 0, l = keys.length; i < l; i++) {
      var key = keys[i]
      cloned[key] = deepClone(obj[key])
    }
    return cloned
  } else {
    return obj
  }
}



export function indexMove(arr, old_index, new_index)
{

    if (new_index >= arr.length)
    {
        let k = new_index - arr.length + 1;

        while (k--)
            arr.push(undefined);
    }

    arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);

    return arr; // for testing
}

//export default deepClone;
