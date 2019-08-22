import axios from "axios";


export default function(Vue, options)
{

    Vue.prototype.$api = class
    {
        static axios()
        {
            return axios.create(
                {
                    //baseURL: "public.php?",
                    withCredentials: false,
                    headers: {
                        "Accepts": "application/json",
                        "Content-Type": "application/json",
                    }
                }
            );
        }

        static getEnvironment()
        {
            return this.axios()
                .get("public.php?/api/environment")
                .then(
                    function (response)
                    {
                        //if(response.data.hasOwnProperty("mode"))
                        //    return response.data.mode;
                        return response.data;
                    }
                )
                .catch(
                    function (error)
                    {
                        console.log(error);
                    }
                );
        }

        static getGroupsAvailable()
        {
            return this.axios()
                .get("public.php?/api/permissions/groups")
                .then(
                    function (response)
                    {
                        let names = [];

                        response.data.forEach(function (group) {
                            names.push(group.name);
                        });

                        return names;
                    }
                )
                .catch(
                    function (error)
                    {
                        console.log(error);
                    }
                );
        }


        static getGroupsAllowed(restrictTo = [])
        {
            return this.axios()
                .get("public.php?/api/permissions/groups/allowed")
                .then(
                    function(response)
                    {
                        let names = [];

                        response.data.forEach(function(group)
                        {
                            if (!Array.isArray(restrictTo) || !restrictTo.length ||
                                restrictTo.includes(group))
                                names.push(group);
                        });

                        return names;
                    }
                )
                .catch(
                    function(error)
                    {
                        console.log(error);
                    }
                );
        }

        static setGroupsAllowed(names)
        {
            return this.axios()
                .post("public.php?/api/permissions/groups/allowed", { groups: names })
                .then(
                    function(response)
                    {
                        return response.data.groups;
                    }
                )
                .catch(
                    function(error)
                    {
                        console.log(error);
                    }
                );
        }


    }


    /*




*/



}
