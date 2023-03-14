export class Url {
    static ACTION_GET = 0;
    static ACTION_REMOVE = 10;
    static ACTION_UPDATE = 20;
    constructor() {
    }

    /**
     * Removes parameter from the URL
     *
     * @param {string} parameter
     */
    removeURLParameter(parameter) {
        this.updateURLParameter(parameter, "", Url.ACTION_REMOVE);
    }

    /**
     * Updates the XREF in the URL
     *
     * @param {string} xref
     */
    changeURLXref(xref) {
        if (xref !== "") {
            this.updateURLParameter("xref", xref, Url.ACTION_UPDATE);
        }
    }

    /**
     * Updates a specified parameter in the URL using the specified action
     *
     * @param {string} parameter
     * @param {string} value
     * @param {number} action
     * @returns {string}
     */
    updateURLParameter(parameter, value, action) {
        let split = document.location.href.split("?");
        let url = split[0];
        if (split.length > 1) {
            let args = split[1];
            let params = new URLSearchParams(args);
            if (params.toString().search(parameter) !== -1) {
                if (action === Url.ACTION_REMOVE) {
                    params.delete(parameter);
                } else if (action === Url.ACTION_UPDATE) {
                    params.set(parameter, value);
                } else if (action === Url.ACTION_GET) {
                    return params.get(parameter);
                }
            }
            history.pushState(null, '', url + "?" + params.toString());
        } else if (action === Url.ACTION_UPDATE) {
            history.pushState(null, '', url + "?" +  parameter + "=" + value);
        }
        return "";
    }

    /**
     * Returns the value of the URL parameter with the given name
     *
     * @param parameter
     * @returns {string}
     */
    getURLParameter(parameter) {
        let result = this.updateURLParameter(parameter, '', Url.ACTION_GET);
        if (result !== null && result !== '') {
            return result.replace('#','');
        } else {
            return '';
        }
    }
}