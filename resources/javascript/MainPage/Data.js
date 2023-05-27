/**
 * Data object to hold data fetching and validation functionality not related to the form
 *
 * @type {{}}
 */
const Data = {
    // Convert image URL to base64 data - we use for embedding images in SVG
    // From https://stackoverflow.com/questions/22172604/convert-image-from-url-to-base64
    getBase64Image: function(img) {
        const canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        return canvas.toDataURL("image/png");
    },

    // Find image URLs and replace with embedded versions
    replaceImageURLs: function(svg, type, img) {
        let startPos, len, url;
        let match = /<image.*xlink:href="http/.exec(svg);
        if (match != null) {
            startPos = match.index+match[0].length-4;
            len = svg.substring(startPos).indexOf("\"");
            url = svg.substring(startPos,startPos+len);
            const img2 = document.createElement("img");
            img2.onload = function() {
                let base64 = Data.getBase64Image(img2);
                svg = svg.replace(url,base64);
                Data.replaceImageURLs(svg, type, img);
                img2.remove();
            }
            img2.src = url.replace(/&amp;/g,"&");
        } else {
            if (type === "svg") {
                const svgBlob = new Blob([svg], {type: "image/svg+xml;charset=utf-8"});
                const svgUrl = URL.createObjectURL(svgBlob);
                Form.downloadLink(svgUrl, download_file_name + "."+type);
            } else {
                img.src = "data:image/svg+xml;utf8," + svg;
            }
        }
    },

    getHelp(help) {
        let request = {
            "type": REQUEST_TYPE_GET_HELP,
            "help_name": help
        };
        let json = JSON.stringify(request);
        return sendRequest(json).then((response) => {
            let responseJson = Data.parseResponse(response);
            if (responseJson) {
                return responseJson.help;
            } else {
                return false;
            }
        });
    },

    parseResponse(response) {
        try {
            let json = JSON.parse(response);
            if (json.success) {
                return json;
            } else {
                return ERROR_CHAR + json.errorMessage;
            }
        } catch(e) {
            UI.showToast(ERROR_CHAR + e);
        }
        return false;
    }
}