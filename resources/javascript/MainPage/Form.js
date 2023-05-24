/**
 * Data object to hold form related functionality
 *
 * @type {{}}
 */
const Form = {
    // Add or remove the % sign from the text input
    togglePercent: function(element, add) {
        // Clicked out of input field, add % sign
        let startValue;
        if (add) {
            // Keep just numbers
            let boxVal = element.value.replace(/\D/g, "");
            // If result is blank, set to default
            if (boxVal === "") {
                boxVal = "100";
            }
            element.value =  boxVal + "%";
        } else {
            // Clicked in input box, remove % and select text,
            // but only select text the first time, let user move cursor if they want
            startValue = element.value;
            element.value = element.value.replace("%", "");
            if (startValue !== element.value) {
                element.select();
            }
        }
    },

    // Update provided element with provided value when element blank
    defaultValueWhenBlank: function(element, value) {
        if (element.value === "") {
            element.value = value;
        }
    },

    checkIndiBlank: function() {
        let el = document.getElementsByClassName("item");
        let list = document.getElementById('xref_list');
        return el.length === 0 && list.value.toString().length === 0;
    },

    // This function ensures that if certain options are checked in regard to which relations to include,
    // then other required options are selected. e.g. if "Anyone" is selected, all other options must
    // all be selected
    updateRelationOption: function(field) {
        // If user clicked "All relatives"
        if (field === "include_all_relatives") {
            // If function triggered by checking "All relatives" field, ensure "Siblings" is checked
            if (document.getElementById("include_all_relatives").checked) {
                document.getElementById("include_siblings").checked = true;
            }
            // If "All relatives" unchecked, uncheck "Anyone"
            if (!document.getElementById("include_all_relatives").checked) {
                document.getElementById("include_all").checked = false;
            }
        }
        // If user clicked "Siblings"
        if (field === "include_siblings") {
            // If function triggered by unchecking "Siblings" field, ensure "All relatives" is unchecked
            if (!document.getElementById("include_siblings").checked) {
                document.getElementById("include_all_relatives").checked = false;
            }
            // If "Siblings" unchecked, uncheck "Anyone"
            if (!document.getElementById("include_siblings").checked) {
                document.getElementById("include_all").checked = false;
            }
        }
        // If user clicked "Spouses"
        if (field === "include_spouses") {
            // If function triggered by checking "All relatives" field, ensure "Siblings" is checked
            if (!document.getElementById("include_siblings").checked) {
                document.getElementById("include_all_relatives").checked = false;
            }
            // If "Spouses" unchecked, uncheck "Anyone"
            if (!document.getElementById("include_spouses").checked) {
                document.getElementById("include_all").checked = false;
            }
        }
        // If function triggered by checking "All relatives" field, ensure everything else is checked
        if (field === "include_all") {
            if (document.getElementById("include_all").checked) {
                document.getElementById("include_all_relatives").checked = true;
                document.getElementById("include_siblings").checked = true;
                document.getElementById("include_spouses").checked = true;
            }
        }
    },

    // Gets position of element relative to another
    // From https://stackoverflow.com/questions/1769584/get-position-of-element-by-javascript
    getPos: function(el, rel)
    {
        let x = 0, y = 0;

        do {
            x += el.offsetLeft;
            y += el.offsetTop;
            el = el.offsetParent;
        }
        while (el !== rel)
        return {x:x, y:y};
    },


    // Toggle items based on if the items in the cart should be used or not
    // enable - if set to true, use cart. Update form to disable options. Set to "false" to reverse.
    toggleCart: function(enable) {
            const el = document.getElementsByClassName("cart_toggle");
            for (let i = 0; i < el.length; i++) {
                el.item(i).disabled = enable;
            }
            Form.showHideClass("cart_toggle_hide", !enable);
            Form.showHideClass("cart_toggle_show", enable);
        },

        // This function is used in Form.toggleCart to show or hide all elements with a certain class,
        // by adding or removing "display: none"
        // css_class - the class to search for
        // show - true to show the elements and false to hide them
        showHideClass: function(css_class, show) {
            let el = document.getElementsByClassName(css_class);
            for (let i = 0; i < el.length; i++) {
                Form.showHide(el.item(i), show)
            }
    },

    // Show or hide an element on the page
    // element - the element to affect
    // show - whether to show (true) or hide (false) the element
    showHide: function(element, show) {
        if (show) {
            element.style.removeProperty("display");
        } else {
            element.style.display = "none";
        }
    },


    showHideMatchCheckbox: function(checkboxId, elementId) {
        Form.showHide(document.getElementById(elementId), document.getElementById(checkboxId).checked);
    },

    showHideMatchDropdown: function(dropdownId, elementId, value) {
        let values = value.split("|");
        let show = false;
        let elValue = document.getElementById(dropdownId).value;
        values.forEach((value) => {
            if (value === elValue) {
                show = true;
            }
        });
        Form.showHide(document.getElementById(elementId),  show);
    },

    showHideSubgroup: function(elementId, callingEl) {
        let callerText = callingEl.innerText;
        let visible = callerText.includes('↓');
        Form.showHide(document.getElementById(elementId), !visible);
        if (visible) {
            callingEl.innerText = callerText.replace('↓', '→');
        } else {
            callingEl.innerText = callerText.replace('→', '↓');
        }

    },

    showHideSearchBox: function(event, visible = null) {
        const el = document.getElementById('diagram_search_box_container');
        // If toggling, set to the opposite of current state
        if (visible === null) {
            visible = el.style.display === "none";
        }
        Form.showHide(el, visible);
        if (visible) {
            // Remove blank section from search box
            tidyTomSelect();
            // Give search box focus
            let dropdown = document.getElementById('diagram_search_box');
            if (typeof dropdown.tomselect !== 'undefined') {
                dropdown.tomselect.focus();
            }
        }
    },


    // Download SVG file
    downloadSVGAsText: function() {
        const svg = document.getElementById('rendering').getElementsByTagName('svg')[0].cloneNode(true);
        svg.removeAttribute("style");
        let svgData = svg.outerHTML.replace(/&nbsp;/g, '');
        // Replace image URLs with embedded data  for SVG also triggers download
        Data.replaceImageURLs(svgData, "svg", null);
    },

    downloadSVGAsPDF: function() {
        Form.downloadSVGAsImage("pdf");
    },

    downloadSVGAsPNG: function() {
        Form.downloadSVGAsImage("png");
    },

    downloadSVGAsJPEG: function() {
        Form.downloadSVGAsImage("jpeg");
    },

    // Download PNG from SVG file
    downloadSVGAsImage: function(type) {
        const svg = document.getElementById('rendering').getElementsByTagName('svg')[0].cloneNode(true);
        // Style attribute used for the draggable browser view, remove this to reset to standard SVG
        svg.removeAttribute("style");

        const canvas = document.createElement("canvas");
        const img = document.createElement("img");
        // get svg data and remove line breaks
        let xml = new XMLSerializer().serializeToString(svg);
        // Fix the + symbol (any # breaks everything)
        xml = xml.replace(/&#45;/g,"+");
        // Replace # colours with rgb equivalent
        // From https://stackoverflow.com/questions/13875974/search-and-replace-hexadecimal-color-codes-with-rgb-values-in-a-string
        const rgbHex = /#([0-9A-F][0-9A-F])([0-9A-F][0-9A-F])([0-9A-F][0-9A-F])/gi;
        xml = xml.replace(rgbHex, function (m, r, g, b) {
            return 'rgb(' + parseInt(r,16) + ','
                + parseInt(g,16) + ','
                + parseInt(b,16) + ')';
        });
        // Replace image URLs with embedded images
        Data.replaceImageURLs(xml, type, img);
        // Once image loaded, draw to canvas then download it
        img.onload = function() {
            canvas.setAttribute('width', img.width.toString());
            canvas.setAttribute('height', img.height.toString());
            // draw the image onto the canvas
            let context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, img.width, img.height);
            // Download it
            const dataURL = canvas.toDataURL('image/'+type);
            if (dataURL.length < 10) {
                UI.showToast(ERROR_CHAR+TRANSLATE['Your browser does not support exporting images this large. Please reduce number of records, reduce DPI setting, or use SVG option.']);
            } else if (type === "pdf") {
                createPdfFromImage(dataURL, img.width, img.height);
            } else {
                Form.downloadLink(dataURL, download_file_name + "." + type);
            }
        }
    },

    // Trigger a download via javascript
    downloadLink: function(URL, filename) {
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

    // Toggle the showing of an advanced settings section
    // button - the button element calling the script
    // id - the id of the element we are toggling
    // visible - whether to make element visible or hidden. Null to toggle current state.
    toggleAdvanced: function(button, id, visible = null) {
        const el = document.getElementById(id);
        // If toggling, set to the opposite of current state
        if (visible === null) {
            visible = el.style.display === "none";
        }
        Form.showHide(el, visible);
        if (visible) {
            button.innerHTML = button.innerHTML.replaceAll('↓','↑');
            const hidden = document.getElementById(id+"-hidden");
            hidden.value = "show";
        } else {
            button.innerHTML = button.innerHTML.replaceAll('↑','↓');
            // Update our hidden field for saving the state
            const hidden = document.getElementById(id+"-hidden");
            hidden.value = "";
        }
    }
}