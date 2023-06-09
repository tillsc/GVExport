/**
 * Data object to hold data fetching and validation functionality not related to the form
 *
 * @type {{}}
 */
const Data = {
    /**
     * Convert image URL to base64 data - we use for embedding images in SVG
     * From https://stackoverflow.com/questions/22172604/convert-image-from-url-to-base64
     *
     * @param img
     * @returns {string}
     */
    getBase64Image: function(img) {
        const canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        return canvas.toDataURL("image/png");
    },

    /**
     * Find image URLs and replace with embedded versions
     *
     * @param svg
     * @param type
     * @param img
     */
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
                Data.download.downloadLink(svgUrl, download_file_name + "."+type);
            } else {
                img.src = "data:image/svg+xml;utf8," + svg;
            }
        }
    },

    /**
     *
     * @param help
     * @returns {Promise<unknown>}
     */
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
    },

    /**
     * Responsible for generating downloads and related activities
     */
    download: {

        /**
         * Request download of SVG file
         */
        downloadSVGAsText() {
            const svg = document.getElementById('rendering').getElementsByTagName('svg')[0].cloneNode(true);
            svg.removeAttribute("style");
            let svgData = svg.outerHTML.replace(/&nbsp;/g, '');
            // Replace image URLs with embedded data  for SVG also triggers download
            Data.replaceImageURLs(svgData, "svg", null);
        },

        /**
         * Request download of PDF file
         */
        downloadSVGAsPDF() {
            Data.download.downloadSVGAsImage("pdf");
        },

        /**
         * Request download of PNG file
         */
        downloadSVGAsPNG() {
            Data.download.downloadSVGAsImage("png");
        },

        /**
         * Request download of JPEG file
         */
        downloadSVGAsJPEG() {
            Data.download.downloadSVGAsImage("jpeg");
        },

        /**
         * Create and trigger download of diagram in the requested image type
         *
         * @param type one of the supported image types
         */
        downloadSVGAsImage(type) {
            const svg = document.getElementById('rendering').getElementsByTagName('svg')[0].cloneNode(true);
            // Style attribute used for the draggable browser view, remove this to reset to standard SVG
            svg.removeAttribute("style");

            const canvas = document.createElement("canvas");
            const img = document.createElement("img");
            // get svg data and remove line breaks
            let xml = new XMLSerializer().serializeToString(svg);
            // Fix the + symbol (any # breaks everything)
            xml = xml.replace(/&#45;/g, "+");
            // Replace # colours with rgb equivalent
            // From https://stackoverflow.com/questions/13875974/search-and-replace-hexadecimal-color-codes-with-rgb-values-in-a-string
            const rgbHex = /#([0-9A-F][0-9A-F])([0-9A-F][0-9A-F])([0-9A-F][0-9A-F])/gi;
            xml = xml.replace(rgbHex, function (m, r, g, b) {
                return 'rgb(' + parseInt(r, 16) + ','
                    + parseInt(g, 16) + ','
                    + parseInt(b, 16) + ')';
            });
            // Replace image URLs with embedded images
            Data.replaceImageURLs(xml, type, img);
            // Once image loaded, draw to canvas then download it
            img.onload = function () {
                canvas.setAttribute('width', img.width.toString());
                canvas.setAttribute('height', img.height.toString());
                // draw the image onto the canvas
                let context = canvas.getContext('2d');
                context.drawImage(img, 0, 0, img.width, img.height);
                // Download it
                const dataURL = canvas.toDataURL('image/' + type);
                if (dataURL.length < 10) {
                    UI.showToast(ERROR_CHAR + TRANSLATE['Your browser does not support exporting images this large. Please reduce number of records, reduce DPI setting, or use SVG option.']);
                } else if (type === "pdf") {
                    Data.download.createPdfFromImage(dataURL, img.width, img.height);
                } else {
                    Data.download.downloadLink(dataURL, download_file_name + "." + type);
                }
            }
        },

        /**
         * Create and download a PDF version of the provided image data
         *
         * @param imgData
         * @param width
         * @param height
         */
        createPdfFromImage(imgData, width, height) {
            const orientation = width >= height ? 'landscape' : 'portrait';
            const dpi = document.getElementById('dpi').value;
            const widthInches = width / dpi;
            const heightInches = height / dpi;
            const doc = new window.jspdf.jsPDF({orientation: orientation, format: [widthInches, heightInches], unit: 'in'});
            doc.addImage(imgData, "PNG", 0, 0, widthInches, heightInches);
            // If running test suite, don't actually trigger download of data
            // We have generated it so know it works
            if (!window.Cypress) {
                doc.save(download_file_name + ".pdf");
            }
        },

        /**
         * Trigger a download via javascript
         *
         * @param URL
         * @param filename
         */
        downloadLink(URL, filename) {
            const downloadLinkElement = document.createElement("a");
            downloadLinkElement.href = URL;
            downloadLinkElement.download = filename;
            document.body.appendChild(downloadLinkElement);
            // If running test suite, don't actually trigger download of data
            // We have generated it so know it works
            if (!window.Cypress) {
                downloadLinkElement.click();
            }
            document.body.removeChild(downloadLinkElement);
        },
    }
}