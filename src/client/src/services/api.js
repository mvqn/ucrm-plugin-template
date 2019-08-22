

import axios from "axios";


export default class API
{

    constructor()
    {
        /*
        this.axios = axios.create({
            baseURL: "public.php?",
            withCredentials: false,
            headers: {
                "Accepts": "application/json",
                "Content-Type": "application/json",
            }
        });
        */
    }

    static axios()
    {
        return axios.create({
            //baseURL: "public.php?",
            withCredentials: false,
            headers: {
                "Accepts": "application/json",
                "Content-Type": "application/json",
            }
        });

    }

    static get(url)
    {
        return API.axios().get(url);
    }

    static post(url, data)
    {
        return API.axios().post(url, data);
    }


    static getGroupsAvailable()
    {
        return API.axios()
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
        return API.axios()
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
        return API.axios()
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
