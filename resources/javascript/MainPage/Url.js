export class Url {
    constructor() {
    }

    removeURLParameter(parameter) {
        this.updateURLParameter(parameter, "", "remove");
    }

    changeURLXref(xref) {
        if (xref !== "") {
            this.updateURLParameter("xref",xref,"update");
        }
    }


    updateURLParameter(parameter, value, action) {
        let split = document.location.href.split("?");
        let url = split[0];
        if (split.length > 1) {
            let args = split[1];
            let params = new URLSearchParams(args);
            if (params.toString().search(parameter) !== -1) {
                if (action === "remove") {
                    params.delete(parameter);
                } else if (action === "update") {
                    params.set(parameter, value);
                } else {
                    return params.get(parameter);
                }
            }
            history.pushState(null, '', url + "?" + params.toString());
        } else if (action === "update") {
            history.pushState(null, '', url + "?" +  parameter + "=" + value);
        }
        return "";
    }

    getURLParameter(parameter) {
        let result = this.updateURLParameter(parameter, "", "get");
        if (result !== null && result !== '') {
            return result.replace('#','');
        } else {
            return null;
        }
    }
}