/**
 * An example object to be imported and mapped to your application's primary store.
 */
export default
{
    // NOTE: Include any state, getters, mutations, actions and modules for your store here...
    state:
    {
        count: 0
    },

    mutations:
    {
        increment: function(state)
        {
            state.count++
        }
    }

    // ...
};
