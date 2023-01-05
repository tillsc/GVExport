const ERROR_CHAR = "E:";
const appendPidTo = function (sourceId, targetId) {
    const ids = [];
    document.getElementById(targetId).value.split(",").forEach(function (id) {
        id = id.trim();
        if (id !== "") {
            ids.push(id);
        }
    });
    const newId = document.getElementById(sourceId).value.trim();
    if (ids.indexOf(newId) === -1) {
        ids.push(newId);
    }
    document.getElementById(targetId).value = ids.join(",");
};


function hideSidebar(e) {
    document.querySelector(".sidebar").hidden = true;
    document.querySelector(".sidebar__toggler").hidden = false;
    e.preventDefault();
}

function showSidebar(e) {
    document.querySelector(".sidebar__toggler").hidden = true;
    document.querySelector(".sidebar").hidden = false;
    e.preventDefault();
}

// Enable or disable the option to add photos.
// This is used when selecting diagram type, as only
// some types support photos.
function togglePhotos(enable) {
    document.getElementById("show_photos").disabled = !enable;
}

// Add or remove the % sign from the text input
function togglePercent(element, add) {
    // Clicked out of input field, add % sign
    let startval;
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
        startval = element.value;
        element.value = element.value.replace("%", "");
        if (startval !== element.value) {
            element.select();
        }
    }
}

// Update provided element with provided value when element blank
function defaultValueWhenBlank(element, value) {
    if (element.value === "") {
        element.value = value;
    }
}

function checkIndiBlank() {
    let el = document.getElementsByClassName("item");
    let list = document.getElementById('xref_list');
    return el.length === 0 && list.value.toString().length === 0;
}

// This function ensures that if certain options are checked in regard to which relations to include,
// then other required options are selected. e.g. if "Anyone" is selected, all other options must
// all be selected
function updateRelationOption(field) {
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

}




// Gets position of element relative to another
// From https://stackoverflow.com/questions/1769584/get-position-of-element-by-javascript
function getPos(el, rel)
{
    let x = 0, y = 0;

    do {
        x += el.offsetLeft;
        y += el.offsetTop;
        el = el.offsetParent;
    }
    while (el !== rel)
    return {x:x, y:y};
}


// Toggle items based on if the items in the cart should be used or not
// enable - if set to true, use cart. Update form to disable options. Set to "false" to reverse.
function toggleCart(enable) {
    const el = document.getElementsByClassName("cart_toggle");
    for (let i = 0; i < el.length; i++) {
        el.item(i).disabled = enable;
    }
    showHideClass("cart_toggle_hide", !enable);
    showHideClass("cart_toggle_show", enable);
}

// This function is used in toggleCart to show or hide all elements with a certain class,
// by adding or removing "display: none"
// css_class - the class to search for
// show - true to show the elements and false to hide them
function showHideClass(css_class, show) {
    let el = document.getElementsByClassName(css_class);
    for (let i = 0; i < el.length; i++) {
        showHide(el.item(i), show)
    }
}

// Show or hide an element on the page
// element - the element to affect
// show - whether to show (true) or hide (false) the element
function showHide(element, show) {
    if (show) {
        element.style.removeProperty("display");
    } else {
        element.style.display = "none";
    }
}

// Hide a displayed element or show a hidden one
function toggleShowID(css_id) {
    const element = document.getElementById(css_id);
    const visible = element.style.display !== "none";
    showHide(element, !visible);
}

// Show a toast message
// message - the message to show
function showToast(message) {
    const toastParent = document.getElementById("toast-container");
    if (toastParent !== null) {
        const toast = document.createElement("div");
        toast.setAttribute("id", "toast");
        if (message.substring(0, ERROR_CHAR.length) === ERROR_CHAR) {
            toast.className += "error";
            message = message.substring(ERROR_CHAR.length);
        }
        toast.innerText = message;
        setTimeout(function () {
            toast.remove();
        }, 5500);
        toastParent.appendChild(toast);
        toast.setAttribute("style", " margin-left: -"+toast.clientWidth/2 + "px; width:" + toast.clientWidth + "px");
        toast.className += " show";
    }
}

// Download SVG file
function downloadSVGAsText() {
    const svg = document.getElementById('rendering').getElementsByTagName('svg')[0].cloneNode(true);
    svg.removeAttribute("style");
    let svgData = svg.outerHTML.replace(/&nbsp;/g, '');
    // Replace image URLs with embedded data  for SVG also triggers download
    replaceImageURLs(svgData, "svg", null);
}

function downloadSVGAsPDF() {
    downloadSVGAsImage("pdf");
}

function downloadSVGAsPNG() {
    downloadSVGAsImage("png");
}

function downloadSVGAsJPEG() {
    downloadSVGAsImage("jpeg");
}

// Download PNG from SVG file
function downloadSVGAsImage(type) {
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
    replaceImageURLs(xml, type, img);
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
            showToast("E:"+CLIENT_ERRORS[0]); // Canvas too big
        } else if (type === "pdf") {
            createPdfFromImage(dataURL, img.width, img.height);
        } else {
            downloadLink(dataURL, download_file_name + "." + type);
        }
    }

}

// Convert image URL to base64 data - we use for embedding images in SVG
// From https://stackoverflow.com/questions/22172604/convert-image-from-url-to-base64
function getBase64Image(img) {
    const canvas = document.createElement("canvas");
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    return canvas.toDataURL("image/png");
}

// Find image URLs and replace with embedded versions
function replaceImageURLs(svg, type, img) {
    let startPos, len, url;
    let match = /<image.*xlink:href="http/.exec(svg);
    if (match != null) {
        startPos = match.index+match[0].length-4;
        len = svg.substring(startPos).indexOf("\"");
        url = svg.substring(startPos,startPos+len);
        const img2 = document.createElement("img");
        img2.onload = function() {
            let base64 = getBase64Image(img2);
            svg = svg.replace(url,base64);
            replaceImageURLs(svg, type, img);
            img2.remove();
        }
        img2.src = url.replace(/&amp;/g,"&");
    } else {
        if (type === "svg") {
            const svgBlob = new Blob([svg], {type: "image/svg+xml;charset=utf-8"});
            const svgUrl = URL.createObjectURL(svgBlob);
            downloadLink(svgUrl, download_file_name + "."+type);
        } else {
            img.src = "data:image/svg+xml;utf8," + svg;
        }
    }
}

// Trigger a download via javascript
function downloadLink(URL, filename) {
    const downloadLink = document.createElement("a");
    downloadLink.href = URL;
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    // If running test suite, don't actually trigger download of data
    // We have generated it so know it works
    if (!window.Cypress) {
        downloadLink.click();
    }
    document.body.removeChild(downloadLink);
}

// Toggle the showing of an advanced settings section
// button - the button element calling the script
// id - the id of the element we are toggling
// visible - whether to make element visible or hidden. Null to toggle current state.
function toggleAdvanced(button, id, visible = null) {
    const el = document.getElementById(id);
    // If toggling, set to the opposite of corrent state
    if (visible === null) {
        visible = el.style.display == "none";
    }
    showHide(el, visible);
    if (visible) {
        button.innerHTML = button.innerHTML.replaceAll("↓","↑");
        const hidden = document.getElementById(id+"-hidden");
        hidden.value = "show";
    } else {
        button.innerHTML = button.innerHTML.replaceAll("↑","↓");
        // Update our hidden field for saving the state
        const hidden = document.getElementById(id+"-hidden");
        hidden.value = "";
    }
}

function setStateFastRelationCheck() {
    document.getElementById("faster_relation_check").disabled = ((!cartempty && document.getElementById("usecart_yes").checked) || !document.getElementById("mark_not_related").checked);
}

function removeURLParameter(parameter) {
    updateURLParameter(parameter, "", "remove");
}

function changeURLXref(xref) {
    if (xref !== "") {
        updateURLParameter("xref",xref,"update");
    }
}
function updateURLParameter(parameter, value, action) {
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

function getURLParameter(parameter) {
    return updateURLParameter(parameter, "", "get").replace("#","");
}

function loadURLXref() {
    const xref = getURLParameter("xref");
    const el = document.getElementById('xref_list');
    if (el.value.replace(",","").trim() === "") {
        el.value = xref;
    } else {
        const xrefs = el.value.split(",");
        if (xrefs.length === 1) {
            el.value = "";
        }
        addIndiToList(xref);
    }
}
function formChanged(autoUpdate) {
    let xref = document.getElementById('pid').value.trim();
    if (xref !== "") {
        addIndiToList(xref);
        changeURLXref(xref);
    }
    let stopXref = document.getElementById('stop_pid').value.trim();
    if (stopXref !== "") {
        addIndiToStopList(stopXref);
    }
    if (autoUpdate) {
        updateRender();
    }
}

function loadXrefList(url, xrefListId, indiListId) {
    let xref_list = document.getElementById(xrefListId).value.trim();
    let xrefs = xref_list.split(",");
    for (let i=0; i<xrefs.length; i++) {
        if (xrefs[i].trim() !== "") {
            loadIndividualDetails(url, xrefs[i], indiListId);
        }
    }
    updateClearAll();
}

function loadIndividualDetails(url, xref, list) {
    fetch(url + xref.trim()).then(async (response) => {
            const data = await response.json();
            let contents;
            if (data["data"].length !== 0) {
                contents = data["data"][0]["text"];
            } else {
                contents = xref;
            }
            const listElement = document.getElementById(list);
            const newListItem = document.createElement("div");
            newListItem.className = "indi_list_item";
            newListItem.setAttribute("data-xref", xref);
            newListItem.setAttribute("onclick", "scrollToRecord('"+xref+"')");
            let otherXrefId;
            if (list === "indi_list") {
                otherXrefId = "xref_list";
            } else {
                otherXrefId = "stop_xref_list";
            }
            newListItem.innerHTML = contents + "<div class=\"remove-item\" onclick=\"removeItem(event, this.parentElement, '" + otherXrefId + "')\"><a href='#'>×</a></div>";
            // Multiple promises can be for the same xref - don't add if a duplicate
            let item = document.querySelector(`[data-xref="${xref}"]`);
            if (item == null) {
                listElement.appendChild(newListItem);
            } else {
                newListItem.remove();
            }
        updateClearAll();
    })
}

function addIndiToList(xref) {
    let list = document.getElementById('xref_list');
    const regex = new RegExp(`(?<=,|^)(${xref})(?=,|$)`);
    if (!regex.test(list.value.replaceAll(" ",""))) {
        appendXrefToList(xref, 'xref_list');
        loadIndividualDetails(TOMSELECT_URL, xref, 'indi_list');

    }
    clearIndiSelect('pid');
}

function addIndiToStopList(xref) {
    let list = document.getElementById('stop_xref_list');
    const regex = new RegExp(`(?<=,|^)(${xref})(?=,|$)`);
    if (!regex.test(list.value.replaceAll(" ",""))) {
        appendXrefToList(xref, 'stop_xref_list');
        loadIndividualDetails(TOMSELECT_URL, xref, 'stop_indi_list');
    }
    clearIndiSelect('stop_pid');
}

function appendXrefToList(xref, elementId) {
    const list = document.getElementById(elementId);
    if (list.value.replace(",","").trim() === "") {
        list.value = xref;
    } else {
        list.value += "," + xref;
        list.value = list.value.replaceAll(",,",",");
    }
}

function clearIndiSelect(selectId) {
    let dropdown = document.getElementById(selectId);
    if (typeof dropdown.tomselect !== 'undefined') {
        dropdown.tomselect.clear();
    } else {
        setTimeout(function () {
            clearIndiSelect(selectId);
        }, 100);
    }
}
function toggleUpdateButton() {
    const updateBtn = document.getElementById('update-browser');
    const autoSettingBox = document.getElementById('auto_update');

    const visible = autoSettingBox.checked;
    showHide(updateBtn, !visible);
    autoUpdate = visible;
    updateRender();
}

function removeItem(e, element, xrefListId) {
    e.stopPropagation();
    let xref = element.getAttribute("data-xref").trim();
    let list = document.getElementById(xrefListId);
    const regex = new RegExp(`(?<=,|^)(${xref})(?=,|$)`);
    list.value = list.value.replaceAll(" ","").replace(regex, "");
    list.value = list.value.replace(",,", ",");
    if (list.value.substring(0,1) === ",") {
        list.value = list.value.substring(1);
    }
    if (list.value.substring(list.value.length-1) === ",") {
        list.value = list.value.substring(0, list.value.length-1);
    }
    element.remove();
    changeURLXref(list.value.split(",")[0].trim());
    updateClearAll();
    if (autoUpdate) {
        updateRender();
    }
}

// clear options from the dropdown if they are already in our list
function removeSelectedOptions() {
    document.getElementById('xref_list').value.split(",").forEach(function (id) {
        id = id.trim();
        if (id !== "") {
            let dropdown = document.getElementById('pid');
            if (typeof dropdown.tomselect !== 'undefined') {
                dropdown.tomselect.removeOption(id);
            }
        }
    });
}

// Clear the list of starting individuals
function clearIndiList() {
    document.getElementById('xref_list').value = "";
    document.getElementById('indi_list').innerHTML = "";
    updateClearAll();
    updateRender();
}
// Clear the list of starting individuals
function clearStopIndiList() {
    document.getElementById('stop_xref_list').value = "";
    document.getElementById('stop_indi_list').innerHTML = "";
    updateClearAll();
    updateRender();
}

// Refresh the list of starting and stopping individuals
function refreshIndisFromXREFS(onchange) {
    // If triggered from onchange event, only proceed if auto-update enabled
    if (!onchange || autoUpdate) {
        document.getElementById('indi_list').innerHTML = "";
        loadXrefList(TOMSELECT_URL, 'xref_list', 'indi_list');
        document.getElementById('stop_indi_list').innerHTML = "";
        loadXrefList(TOMSELECT_URL, 'stop_xref_list', 'stop_indi_list');
    }
}

// Trigger clearAll update for each instance
function updateClearAll() {
    updateClearAllElements('clear_list', 'indi_list');
    updateClearAllElements('clear_stop_list', 'stop_indi_list');
}

// Show or hide Clear All options based on check
function updateClearAllElements(clearElementId, listItemElementId) {
    let clearElement = document.getElementById(clearElementId);
    let listItemElement = document.getElementById(listItemElementId);
    let listItems = listItemElement.getElementsByClassName('indi_list_item');
    if (listItems.length > 1) {
        showHide(clearElement, true);
    } else {
        showHide(clearElement, false);
    }
}

// Toggle full screen for element
// Modified from https://stackoverflow.com/questions/7130397/how-do-i-make-a-div-full-screen
function toggleFullscreen() {
    // If already fullscreen, exit fullscreen
    if (
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement
    ) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    } else { // Not full screen, so go fullscreen
        const element = document.getElementById('render-container');
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }
}

// Add a listener to trigger when the user goes fullscreen or exits fullscreen
function handleFullscreen() {
    if (document.addEventListener)
    {
        document.addEventListener('fullscreenchange', handleFullscreenExit, false);
        document.addEventListener('mozfullscreenchange', handleFullscreenExit, false);
        document.addEventListener('MSFullscreenChange', handleFullscreenExit, false);
        document.addEventListener('webkitfullscreenchange', handleFullscreenExit, false);
    }
}

// This function is run when the fullscreen state is changed
function handleFullscreenExit()
{
    if (!document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement)
    {
        showHide(document.getElementById("fullscreenButton"), true);
        showHide(document.getElementById("fullscreenClose"), false);
    } else {
        showHide(document.getElementById("fullscreenButton"), false);
        showHide(document.getElementById("fullscreenClose"), true);
    }
}

// Get the computed property of an element
function getComputedProperty(element, property) {
    const style = getComputedStyle(element);
    return (parseFloat(style.getPropertyValue(property)));
}

// Create and download a PDF version of the provided image
function createPdfFromImage(imgData, width, height) {
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
}

// If the browser render is available, scroll to the xref provided (if it exists)
function scrollToRecord(xref) {
    const rendering = document.getElementById('rendering');
    const svg = rendering.getElementsByTagName('svg')[0].cloneNode(true);
    let titles = svg.getElementsByTagName('title');
    for (let i=0; i<titles.length; i++) {
        let xrefs = titles[i].innerHTML.split("_");
        for (let j=0; j<xrefs.length; j++) {
            if (xrefs[j] === xref) {
                let minX = null;
                let minY = null;
                let maxX = null;
                let maxY = null;
                const group = titles[i].parentElement;
                // We need to locate the element within the SVG. We use "polygon" here because it is the
                // only element that will always exist and that also has position information
                // (other elements like text, image, etc. can be disabled by the user)
                const points = group.getElementsByTagName('polygon')[0].getAttribute('points').split(" ");
                // Find largest and smallest X and Y value out of all the points of the polygon
                for (j = 0; j < points.length; j++) {
                    const x = parseFloat(points[j].split(",")[0]);
                    const y = parseFloat(points[j].split(",")[1]);
                    if (minX === null || x < minX) {
                        minX = x;
                    }
                    if (minY === null || y < minY) {
                        minY = y;
                    }
                    if (maxX === null || x > maxX) {
                        maxX = x;
                    }
                    if (maxY === null || y > maxY) {
                        maxY = y;
                    }
                }
                // Get the average of the largest and smallest, so we can position the element in the middle
                let x = (minX + maxX) / 2;
                let y = (minY + maxY) / 2;
                // Why do we multiply the scale by 1 and 1/3?
                let zoombase = panzoomInst.getTransform().scale * (1 + 1 / 3);
                let zoom = zoombase * parseFloat(document.getElementById("dpi").value)/72;
                panzoomInst.smoothMoveTo((rendering.offsetWidth / 2) - x * zoom, (rendering.offsetHeight / 2) - parseFloat(svg.getAttribute('height')) * zoombase - y * zoom);
                return true;
            }
        }
    }
    return false;
}

// Return distance between two points
function getDistance(x1, y1, x2, y2){
    let x = x2 - x1;
    let y = y2 - y1;
    return Math.sqrt(x * x + y * y);
}

function handleTileClick() {
    const MIN_DRAG = 100;
    let startx;
    let starty;

    let linkElements = document.querySelectorAll("svg a");
    for (let i = 0; i < linkElements.length; i++) {
        linkElements[i].addEventListener("mousedown", function(e) {
            startx = e.clientX;
            starty = e.clientY;
        });
        // Only trigger links if not dragging
        linkElements[i].addEventListener("click", function(e) {
            if (getDistance(startx, starty, e.clientX, e.clientY) >= MIN_DRAG) {
                e.preventDefault();
            }
        });
    }
}

// This function is run when the page is loaded
function pageLoaded() {
    TOMSELECT_URL = document.getElementById('pid').getAttribute("data-url") + "&query=";
    loadURLXref();
    loadXrefList(TOMSELECT_URL, 'xref_list', 'indi_list');
    loadXrefList(TOMSELECT_URL, 'stop_xref_list', 'stop_indi_list');
    // Remove reset parameter from URL when page loaded, to prevent
    // further resets when page reloaded
    removeURLParameter("reset");
    // Remove options from selection list if already selected
    setInterval(function () {removeSelectedOptions()}, 100);
    // Listen for fullscreen change
    handleFullscreen();
    // Load browser render when page has loaded
    updateRender();

    document.querySelector(".hide-form").addEventListener("click", hideSidebar);

    document.querySelector(".sidebar__toggler a").addEventListener("click", showSidebar);

    document.addEventListener("keydown", function(e) {
        if (e.key === "Esc" || e.key === "Escape") {
            document.querySelector(".sidebar").hidden ? showSidebar(e) : hideSidebar(e);
        }
    });
}

// Function to show a help message
// item - the help item identifier
function showHelp(item) {
    let helpText = getHelpText(item);
    const modal = document.createElement("div");
    modal.className = "modal";
    modal.innerHTML = "<div class=\"modal-content\">\n" +
        "<span class='close' onclick='this.parentElement.parentElement.remove()'>&times;</span>\n" +
        "<p>" + helpText + "</p>\n" +
        "</div>"
    document.body.appendChild(modal);
    // When the user clicks anywhere outside the modal, close it
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    }
    return false;
}

/**
 * Downloads settings as JSON file
 */
function downloadSettingsFile(reloadSettings) {
    if (reloadSettings) {
        settings_json = "";
        updateRender();
    }

    setTimeout(() => {
        if (settings_json !== "") {
            let file = new Blob([settings_json], {type: "text/plain"});
            let url = URL.createObjectURL(file);
            downloadLink(url, TREE_NAME + ".json")
        } else {
            downloadSettingsFile(false);
        }
    }, 100);
}

/**
 * Loads settings from uploaded file
 */
function uploadSettingsFile(input) {
    if (input.files.length === 0) {
        return;
    }
    const file = input.files[0];
    let reader = new FileReader();
    reader.onload = (e) => {
        loadSettings(e.target.result);
    };
    reader.onerror = (e) => showToast(e.target.error.name);
    reader.readAsText(file);
}

function loadSettings(data) {
    let settings;
    try {
        settings = JSON.parse(data);
    } catch (e) {
        showToast("Failed to load settings: " + e);
        return false;
    }
    Object.keys(settings).forEach (function(key){
        let el = document.getElementById(key);
        if (el == null) {
            switch (key) {
                case 'diagram_type':
                    setCheckStatus(document.getElementById('diagtype_simple'), settings[key] === 'simple');
                    setCheckStatus(document.getElementById('diagtype_decorated'), settings[key] === 'decorated');
                    setCheckStatus(document.getElementById('diagtype_combined'), settings[key] === 'combined');
                    break;
                case 'birthdate_year_only':
                    setCheckStatus(document.getElementById('bd_type_y'), settings[key]);
                    setCheckStatus(document.getElementById('bd_type_gedcom'), !settings[key]);
                    break;
                case 'death_date_year_only':
                    setCheckStatus(document.getElementById('dd_type_y'), settings[key]);
                    setCheckStatus(document.getElementById('dd_type_gedcom'), !settings[key]);
                    break;
                case 'marr_date_year_only':
                    setCheckStatus(document.getElementById('md_type_y'), settings[key]);
                    setCheckStatus(document.getElementById('md_type_gedcom'), !settings[key]);
                    break;
                case 'show_adv_people':
                    toggleAdvanced(document.getElementById('people-advanced-button'), 'people-advanced', settings[key]);
                    break;
                case 'show_adv_appear':
                    toggleAdvanced(document.getElementById('appearance-advanced-button'), 'appearance-advanced', settings[key]);
                    break;
                case 'show_adv_files':
                    toggleAdvanced(document.getElementById('files-advanced-button'), 'files-advanced', settings[key]);
                    break;
                // If option to use cart is not showing, don't load, but also don't show error
                case 'use_cart':
                // These options only exist if debug panel active - don't show error if not found
                case 'enable_debug_mode':
                case 'enable_graphviz':
                    break;
                default:
                    showToast("E:" + CLIENT_ERRORS[1] + " " + key);
            }
        } else {
            if (el.type === 'checkbox' || el.type === 'radio') {
                setCheckStatus(el, settings[key]);
            } else {
                el.value = settings[key];
            }
        }
    });
    setStateFastRelationCheck();
    showHide(document.getElementById('arrow_group'),document.getElementById('colour_arrow_related').checked)
    showHide(document.getElementById('startcol_option'),document.getElementById('highlight_start_indis').checked)
    refreshIndisFromXREFS(false);
    if (autoUpdate) updateRender();
}

function setCheckStatus(el, checked) {
    if (checked) {
        el.setAttribute('checked', 'true');
    } else {
        el.removeAttribute('checked');
    }
}

function setGraphvizAvailable(available) {
    graphvizAvailable = available;
}

/**
 * This function exists for automated testing to access settings JSON without having to download the file
 *
 * @returns {string}
 */
function getSettingsJson() {
    return settings_json;
}