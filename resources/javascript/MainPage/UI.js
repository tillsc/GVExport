/**
 * UI object to hold UI functionality not related to the form
 *
 * @type {{}}
 */
const UI = {

    /**
     * Hides the settings panel
     */
    hideSidebar: function() {
        document.querySelector(".sidebar").hidden = true;
        document.querySelector(".sidebar_toggle").hidden = false;
        document.getElementById('help-content').innerHTML = '';
    },

    /**
     * Displays the settings panel
     */
    showSidebar: function() {
        document.querySelector(".sidebar_toggle").hidden = true;
        document.querySelector(".sidebar").hidden = false;
    },


    /**
     * Shows a pop-up message
     *
     * @param message
     */
    showToast: function(message) {
        const toastParent = document.getElementById("toast-container");
        if (toastParent !== null) {
            const toast = document.createElement("div");
            toast.setAttribute("id", "toast");
            toast.setAttribute("class", "pointer");
            if (message.substring(0, ERROR_CHAR.length) === ERROR_CHAR) {
                toast.className += "error";
                message = message.substring(ERROR_CHAR.length);
            }
            toast.innerText = message;
            let msg = [];
            msg[0] = new Date();
            msg[1] = message;
            messageHistory.push(msg);
            setTimeout(function () {
                toast.remove();
            }, 5500);
            toastParent.appendChild(toast);
            toast.setAttribute("style", " margin-left: -"+toast.clientWidth/2 + "px; width:" + toast.clientWidth + "px");
            toast.setAttribute("onclick", "return showHelp('message_history');");
            toast.className += " show";
        }
    },

    // If the browser render is available, scroll to the xref provided (if it exists)
    scrollToRecord(xref) {
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
                    let x = null;
                    let y = null;
                    const group = titles[i].parentElement;
                    // We need to locate the element within the SVG. We use "polygon" here because it is the
                    // only element that will always exist and that also has position information
                    // (other elements like text, image, etc. can be disabled by the user)
                    const polygonList = group.getElementsByTagName('polygon');
                    let points;
                    if (polygonList.length !== 0) {
                        points = polygonList[0].getAttribute('points').split(" ");
                        // Find largest and smallest X and Y value out of all the points of the polygon
                        for (let k = 0; k < points.length; k++) {
                            // If path instructions, ignore
                            if (points[k].replace(/[a-z]/gi, '') !== points[k]) break;
                            const x = parseFloat(points[k].split(',')[0]);
                            const y = parseFloat(points[k].split(',')[1]);
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
                        x = (minX + maxX) / 2;
                        y = (minY + maxY) / 2;
                    } else {
                        x = group.getElementsByTagName('text')[0].getAttribute('x');
                        y = group.getElementsByTagName('text')[0].getAttribute('y')
                    }

                    // Why do we multiply the scale by 1 and 1/3?
                    let zoombase = panzoomInst.getTransform().scale * (1 + 1 / 3);
                    let zoom = zoombase * parseFloat(document.getElementById("dpi").value)/72;
                    panzoomInst.smoothMoveTo((rendering.offsetWidth / 2) - x * zoom, (rendering.offsetHeight / 2) - parseFloat(svg.getAttribute('height')) * zoombase - y * zoom);
                    return true;
                }
            }
        }
        return false;
    },

    tile: {
        /**
         * Fixes URL so regular expression doesn't get confused
         *
         * @param url
         * @returns {string}
         */
        cleanUrl(url){
            if (url) {
                return url.replaceAll('%2F', '/');
            } else {
                return '';
            }
        },

        /**
         * Check if the SVG node has <A> tags with a URL with '/individual/' in it.
         * @param node
         * @returns {boolean}
         */
        isNodeAnIndividual(node) {
            if (this.cleanUrl(node.getAttribute('xlink:href')).indexOf('/individual/') !== -1) {
                return true;
            }
            // Also check children
            for (let i = 0; i < node.childNodes.length; i++) {
                const child = node.childNodes[i];
                if (child.tagName && child.tagName.toLowerCase() === 'a') {
                    if (this.cleanUrl(node.getAttribute('xlink:href')).indexOf('/individual/') !== -1) {
                        return true;
                    }
                }
                // Recursively check child nodes
                if (child.childNodes.length > 0) {
                    if (this.isNodeAnIndividual(child)) {
                        return true;
                    }
                }
            }

            return false;
        },

        /**
         * Takes a webtrees individual's URL as input, and returns their XREF
         *
         * @param url
         * @returns {*}
         */
        getXrefFromUrl(url) {
            url = this.cleanUrl(url);
            const regex = /\/tree\/[^/]+\/individual\/(.+)\//;
            return url.match(regex)[1];
        },

        /**
         * Add event listeners to handle clicks on the individuals and family nodes in the diagram
         */
        handleTileClick() {
            const MIN_DRAG = 100;
            const DEFAULT_ACTION = '0';
            let startx;
            let starty;

            let linkElements = document.querySelectorAll("svg a");
            linkElements = Array.from(linkElements).filter(function (aTag) {
                return aTag.hasAttribute('xlink:href');
            });

            for (let i = 0; i < linkElements.length; i++) {
                linkElements[i].addEventListener("mousedown", function (e) {
                    startx = e.clientX;
                    starty = e.clientY;
                });
                // Only trigger links if not dragging
                linkElements[i].addEventListener('click', function (e) {
                    let clickActionEl = document.getElementById('click_action_indi');
                    let clickAction = clickActionEl ? clickActionEl.value : DEFAULT_ACTION;
                    let url = linkElements[i].getAttribute('xlink:href');

                    // Do nothing if user is dragging
                    if (Data.getDistance(startx, starty, e.clientX, e.clientY) >= MIN_DRAG) {
                        e.preventDefault();
                    // Leave family links alone
                    } else if (clickAction !== '0' && UI.tile.isNodeAnIndividual(linkElements[i])) {
                        e.preventDefault();
                        let xref = UI.tile.getXrefFromUrl(url);
                        switch (clickAction) {
                            case '10': // Add to list of starting individuals
                                UI.tile.addIndividualToStartingIndividualsList(xref);
                                break;
                            case '20': // Remove list of starting individuals and have just this person
                                if (xref) {
                                    Form.indiList.clearIndiList(false);
                                    Form.indiList.addIndiToList(xref);
                                    mainPage.Url.changeURLXref(xref);
                                    handleFormChange();
                                }
                                break;
                            case '30':// Add to list of stopping individuals
                                if (xref) {
                                    Form.stoppingIndiList.addIndiToStopList(xref);
                                    handleFormChange();
                                }
                                break;
                            case '40':// Remove list of stopping individuals and have just this person
                                if (xref) {
                                    Form.stoppingIndiList.clearStopIndiList(false);
                                    Form.indiList.addIndiToList(xref);
                                    mainPage.Url.changeURLXref(xref);
                                    handleFormChange();
                                }
                                break;
                            case '50': // Show a menu for user to choose
                                UI.tile.showNodeContextMenu(e, url, xref);
                                break;
                            // Do nothing - default click action is fine
                            case '0': // Allow link to trigger user page opening
                            case '60': // Do nothing option
                            default: // Unknown, so do nothing
                                break;
                        }
                    }

                });
            }
        },

        /**
         * Shows a context menu on a node in the diagram, e.g. show menu when individual clicked if this option enabled
         *
         * @param e The click event
         * @param url The URL of the individual or family webtrees page
         * @param xref The xref of the individual or family
         */
        showNodeContextMenu(e, url, xref) {
            const div = document.getElementById('context_menu');
            div.setAttribute("data-xref",  xref);
            div.setAttribute("data-url",  url);
            UI.contextMenu.enableContextMenu(window.innerWidth - e.clientX, e.clientY);
            UI.contextMenu.addContextMenuOption('👤', 'Open individual\'s page', UI.tile.openIndividualsPageContextMenu);
            UI.contextMenu.addContextMenuOption('➕', 'Add individual to list of starting individuals', UI.tile.addIndividualToStartingIndividualsContextMenu);
            UI.contextMenu.addContextMenuOption('🔄', 'Replace starting individuals with this individual', UI.tile.replaceStartingIndividualsContextMenu);
            UI.contextMenu.addContextMenuOption('🛑', 'Add this individual to the list of stopping individuals', UI.tile.addIndividualToStoppingIndividualsContextMenu);
            UI.contextMenu.addContextMenuOption('🚫', 'Replace stopping individuals with this individual', UI.tile.replaceStoppingIndividualsContextMenu);
        },

        /**
         * Function for context menu item
         *
         * @param e Click event
         */
        openIndividualsPageContextMenu(e) {
            UI.tile.openIndividualsPage(e.currentTarget.parentElement.getAttribute('data-url'));
        },

        /**
         * Function for context menu item
         *
         * @param e Click event
         */
        addIndividualToStartingIndividualsContextMenu(e) {
            UI.tile.addIndividualToStartingIndividualsList(e.currentTarget.parentElement.getAttribute('data-xref'));
        },

        /**
         * Function for context menu item
         *
         * @param e Click event
         */
        replaceStartingIndividualsContextMenu(e) {
            UI.tile.replaceStartingIndividuals(e.currentTarget.parentElement.getAttribute('data-xref'));
        },

        /**
         * Function for context menu item
         *
         * @param e Click event
         */
        addIndividualToStoppingIndividualsContextMenu(e) {
            UI.tile.addIndividualToStoppingIndividualsList(e.currentTarget.parentElement.getAttribute('data-xref'));
        },

        /**
         * Function for context menu item
         *
         * @param e Click event
         */
        replaceStoppingIndividualsContextMenu(e) {
            UI.tile.replaceStoppingIndividuals(e.currentTarget.parentElement.getAttribute('data-xref'));
        },


        /**
         * Adds the individual to the starting individual list
         *
         * @param xref
         */
        openIndividualsPage(url) {
            if (url) {
                window.open(url,'_blank');
                UI.contextMenu.clearContextMenu();
            }
        },

        /**
         * Adds the individual to the starting individual list
         *
         * @param xref
         */
        addIndividualToStartingIndividualsList(xref) {
            if (xref) {
                Form.indiList.addIndiToList(xref);
                handleFormChange();
                UI.contextMenu.clearContextMenu();
            }
        },

        /**
         * Replaces starting individual list with this individual
         *
         * @param xref
         */
        replaceStartingIndividuals(xref) {
            if (xref) {
                Form.indiList.clearIndiList(false);
                Form.indiList.addIndiToList(xref);
                mainPage.Url.changeURLXref(xref);
                handleFormChange();
                UI.contextMenu.clearContextMenu();
            }
        },

        /**
         * Adds the individual to the stopping individual list
         *
         * @param xref
         */
        addIndividualToStoppingIndividualsList(xref) {
            if (xref) {
                Form.stoppingIndiList.addIndiToStopList(xref);
                handleFormChange();
                UI.contextMenu.clearContextMenu();
            }
        },

        /**
         * Replaces stopping individual list with this individual
         *
         * @param xref
         */
        replaceStoppingIndividuals(xref) {
            if (xref) {
                Form.stoppingIndiList.clearStopIndiList(false);
                Form.stoppingIndiList.addIndiToStopList(xref);
                handleFormChange();
                UI.contextMenu.clearContextMenu();
            }
        },

        /**
         * Run when setting is changed for what to do when individual is clicked in diagram
         */
        clickOptionChanged() {
            // Trigger background settings saving.
            isUserLoggedIn().then((loggedIn) => {
                if (loggedIn) {
                    saveSettingsServer().then();
                } else {
                    Data.storeSettings.saveSettingsClient(ID_MAIN_SETTINGS).then();
                }
            });
        }

    },
    /**
     * Additional side panel that shows help information
     */
    helpPanel: {

        /**
         * Run startup code when help panel created
         */
        init() {
            document.querySelector('.hide-help').addEventListener('click', UI.helpPanel.hideHelpSidebar);
            document.querySelector('.help-toggle a').addEventListener('click', UI.helpPanel.clickHelpSidebarButton);
            document.querySelector('.btn-help-home').addEventListener('click', UI.helpPanel.loadHelpHome);
            document.querySelector('#help-about').addEventListener('click', UI.helpPanel.loadHelpAbout);
            let helpContentElement = document.querySelector('#help-content');
            helpContentElement.addEventListener('click', UI.helpPanel.handleHelpContentClick);
            UI.helpPanel.loadHelp('Home').catch(function (error){
                UI.showToast(ERROR_CHAR + error);
            });
        },

        /**
         * Handle event when button to show sidebar is clicked
         */
        clickHelpSidebarButton() {
            UI.helpPanel.showHelpSidebar();
        },

        /**
         * Displays the help side panel
         *
         * @param help
         */
        showHelpSidebar(help = '') {
            UI.helpPanel.loadHelp(help).then(() => {
                document.querySelector(".help-toggle").hidden = true;
                document.querySelector(".help-sidebar").hidden = false;
            })
        },

        /**
         * Hides the help sidebar
         */
        hideHelpSidebar() {
            document.querySelector(".help-sidebar").hidden = true;
            document.querySelector(".help-toggle").hidden = false;
        },

        /**
         * Handle click on help content form
         *
         * @param event
         */
        handleHelpContentClick(event) {
            if (event.target.tagName === 'A') {
                UI.helpPanel.showHelpSidebar(event.target.getAttribute('data-name'));
            }
        },

        /**
         * Reverts the help panel back to the home page
         */
        loadHelpHome() {
            UI.helpPanel.loadHelp('Home');
        },

        /**
         * Shows the About GVExport page when Help button clicked
         */
        loadHelpAbout(event) {
            event.preventDefault();
            UI.helpPanel.showHelpSidebar('About GVExport');
        },

        /**
         * Send request to server to retrieve help information then
         * adds response into page
         *
         * @param help The name of the help we want to load
         */
        loadHelp(help) {
            if (help !== '') {
                return Data.getHelp(help).then(function (response) {
                    if (response) {
                        let contentEl = document.getElementById('help-content');
                        contentEl.innerHTML = Data.decodeHTML(response);
                        contentEl.scrollTop = 0;
                    } else {
                        setTimeout(function(){location.reload();}, 3000);
                        UI.showToast(ERROR_CHAR + TRANSLATE['Login expired. Reloading page...']);
                    }
                });
            } else {
                return Promise.resolve();
            }
        },

        /**
         * Triggered when help icon is clicked
         *
         * @param event
         */
        clickInfoIcon(event) {
            event.stopPropagation();
            event.preventDefault();
            UI.helpPanel.showHelpSidebar(event.target.getAttribute('data-help'));
        }
    },

    /**
     * When page loaded, make changes if theme chosen doesn't work nicely
     *
     */
    fixTheme() {
        let elements = document.querySelectorAll('.advanced-settings-btn');
        let baseColour = getComputedStyle(document.querySelector('.wt-page-options-value')).backgroundColor;
        let replaceColour = getComputedStyle(document.querySelector('.btn-primary')).backgroundColor;
        let primaryButton = document.querySelector('.btn-primary');
        let replaceTextColour;
        if (primaryButton !== null) {
            replaceTextColour = getComputedStyle(primaryButton).color;
        }
        for (let i=0; i<elements.length; i++) {
            if (getComputedStyle(elements[i]).backgroundColor === baseColour) {
                // If no background-color because background is used instead, then use this.
                // Resolves issue where background-color is transparent because background gradient being used
                if (replaceColour === 'rgba(0, 0, 0, 0)') {
                    let searchButton = document.querySelector('.wt-header-search-button');
                    if (searchButton !== null) {
                        elements[i].style.background = getComputedStyle(searchButton).background;
                    }
                } else {
                    elements[i].style.backgroundColor = replaceColour;
                }
                if (primaryButton !== null) {
                    elements[i].style.color = replaceTextColour;
                }
            }
        }
    },

    /**
     * Represents the context menu that is shown when an individual is selected and the option to show a menu is enabled
     */
    contextMenu: {
        /**
         * Adds the context menu element - must only be run once (say, on page load)
         */
        init() {
            let div = document.createElement('div');

            div.setAttribute('id', 'context_menu');
            div.style.display = 'block';
            document.getElementById('render-container').appendChild(div);
        },

        /**
         * Enables the context menu at the provided page location
         *
         * @param x
         * @param y
         */
        enableContextMenu(x, y) {
            UI.contextMenu.clearContextMenu();
            const div = document.getElementById('context_menu');
            // Adjustment so pointy bit of menu is on mouse click position
            x -= 8;
            y += 5;
            // Set position
            div.style.position = 'fixed';
            div.style.right = x + 'px';
            div.style.top = y + 'px';
            div.style.display = '';
        },

        /**
         * Removes items from context menu and hides it
         */
        clearContextMenu() {
            const div = document.getElementById('context_menu');
            div.innerHTML = '';
            div.style.display = 'none';
        },

        /**
         * Adds an option to the context menu list
         *
         * @param emoji Emoji to show at the start of line before text
         * @param text The text of the option to show
         * @param callback The function to call when option is selected
         */
        addContextMenuOption(emoji, text, callback) {
            const div = document.getElementById('context_menu');
            let el = document.createElement('a');
            el.setAttribute('class', 'settings_ellipsis_menu_item');
            el.innerHTML = '<span class="settings_ellipsis_menu_icon">' + emoji + '</span><span>' + TRANSLATE[text] + '</span>';
            div.appendChild(el);
            el.addEventListener("click", (e) => {
                callback(e);
            });
        }
    },

    /**
     * UI functionality for Saved Settings options
     */
    savedSettings: {

        /**
         * Display context menu for item in saved settings list
         *
         * @param event
         */
        showSavedSettingsItemMenu(event) {
            event.stopImmediatePropagation();
            let id = event.target.parentElement.parentElement.getAttribute('data-id');
            let token = event.target.parentElement.parentElement.getAttribute('data-token');
            removeSettingsEllipsisMenu(event.target);
            isUserLoggedIn().then((loggedIn) => {
                if (id != null) {
                    id = id.trim();
                    let div = document.createElement('div');
                    div.setAttribute('class', 'settings_ellipsis_menu');
                    UI.savedSettings.addSettingsMenuOption(id, div, '❌', 'Delete', UI.savedSettings.deleteSettingsMenuAction);
                    UI.savedSettings.addSettingsMenuOption(id, div, '💻', 'Download', UI.savedSettings.downloadSettingsFileMenuAction);
                    UI.savedSettings.addSettingsMenuOption(id, div, '🏷️', 'Rename', UI.savedSettings.renameSettingsMenuAction);
                    if (loggedIn) {
                        UI.savedSettings.addSettingsMenuOption(id, div, '🔗', 'Copy link', UI.savedSettings.copySavedSettingsLinkMenuAction);
                        if (token !== '') {
                            UI.savedSettings.addSettingsMenuOption(id, div, '🚫', 'Revoke link', UI.savedSettings.revokeSavedSettingsLinkMenuAction, token);
                        }
                        if (MY_FAVORITES_MODULE_ACTIVE) {
                            UI.savedSettings.addSettingsMenuOption(id, div, '🌟', 'Add to My favourites', UI.savedSettings.addUrlToMyFavouritesMenuAction);
                        }
                        if (TREE_FAVORITES_MODULE_ACTIVE) {
                            UI.savedSettings.addSettingsMenuOption(id, div, '🌲', 'Add to Tree favourites', UI.savedSettings.addUrlToTreeFavourites);
                        }
                    }
                    event.target.appendChild(div);
                }
            });
        },

        /**
         * Add an item to the saved settings item context menu
         *
         * @param id
         * @param div
         * @param emoji
         * @param text
         * @param callback
         * @param token
         */
        addSettingsMenuOption(id, div, emoji, text, callback, token = '') {
            let el = document.createElement('a');
            el.setAttribute('class', 'settings_ellipsis_menu_item');
            el.innerHTML = '<span class="settings_ellipsis_menu_icon">' + emoji + '</span><span>' + TRANSLATE[text] + '</span>';
            el.id = id;
            el.token = token;
            el.addEventListener("click", (e) => {
                callback(e);
            });
            div.appendChild(el);
        },

        /**
         * Trigger rename option from saved setting context menu
         *
         * @param e
         */
        renameSettingsMenuAction(e) {
            e.stopPropagation();
            let id = e.currentTarget.id;
            Data.savedSettings.renameSetting(id);
        },

        /**
         * Downloads settings as JSON file
         */
        downloadSettingsFileMenuAction(event) {
            let parent = event.target.parentElement;
            while (!parent.dataset.settings) {
                parent = parent.parentElement;
            }
            let settings_json_string = parent.dataset.settings;
            let settings;
            try {
                settings = JSON.parse(settings_json_string);
            } catch (e) {
                UI.showToast("Failed to load settings: " + e);
                return false;
            }
            let file = new Blob([settings_json_string], {type: "text/plain"});
            let url = URL.createObjectURL(file);
            Data.download.downloadLink(url, TREE_NAME + " - " + settings['save_settings_name'] + ".json")
        },

        /**
         * Trigger delete option from saved setting context menu
         *
         * @param e
         */
        async deleteSettingsMenuAction(e) {
            e.stopPropagation();
            try {
                const id = e.currentTarget.id;
                const loggedIn = await isUserLoggedIn();

                if (loggedIn) {
                    await Data.savedSettings.deleteSettingsServer(id);
                    loadSettingsDetails();
                } else {
                    Data.savedSettings.deleteSettingsClient(id);
                    loadSettingsDetails();
                }
            } catch (error) {
                UI.showToast("Failed to delete settings: " + error);
            }
        },

        /**
         * Trigger "copy link" option from saved setting context menu
         *
         * @param e
         */
        async copySavedSettingsLinkMenuAction(e) {
            e.stopPropagation();
            const id = e.currentTarget.id;
            try {
                const url = await Data.savedSettings.getSavedSettingsLink(id);
                await copyToClipboard(url);
                UI.showToast(TRANSLATE['Copied link to clipboard']);
            } catch (error) {
                console.error('Error copying saved settings link:', error);
                UI.showToast(TRANSLATE['Failed to copy link to clipboard']);
                showModal(`<p>${TRANSLATE['Failed to copy link to clipboard']}. ${TRANSLATE['Copy manually below']}:</p><textarea style="width: 100%">${url}</textarea>`);
            }
        },

        /**
         * Trigger option to revoke a shared link from saved setting context menu (after sharing)
         *
         * @param e
         */
        revokeSavedSettingsLinkMenuAction(e) {
            e.stopPropagation();
            let token = e.currentTarget.token;
            isUserLoggedIn().then((loggedIn) => {
                if (loggedIn) {
                    let request = {
                        "type": REQUEST_TYPE_REVOKE_SAVED_SETTINGS_LINK,
                        "token": token
                    };
                    let json = JSON.stringify(request);
                    sendRequest(json).then((response) => {
                        loadSettingsDetails();
                        try {
                            let json = JSON.parse(response);
                            if (json.success) {
                                UI.showToast(TRANSLATE['Revoked access to shared link']);
                            } else {
                                UI.showToast(ERROR_CHAR + json.errorMessage);
                            }
                        } catch (e) {
                            UI.showToast("Failed to load response: " + e);
                            return false;
                        }
                    });
                }
            });
        },

        /**
         * Trigger option to add link to My Favourites webtrees page, from saved setting context menu
         *
         * @param e
         */
        addUrlToMyFavouritesMenuAction(e) {
            e.stopPropagation();
            let id = e.currentTarget.id;
            isUserLoggedIn().then((loggedIn) => {
                if (loggedIn) {
                    let request = {
                        "type": REQUEST_TYPE_ADD_MY_FAVORITE,
                        "settings_id": id
                    };
                    let json = JSON.stringify(request);
                    sendRequest(json).then((response) => {
                        try {
                            let json = JSON.parse(response);
                            if (json.success) {
                                UI.showToast(TRANSLATE['Added to My favourites']);
                            } else {
                                UI.showToast(ERROR_CHAR + json.errorMessage);
                            }
                        } catch (e) {
                            UI.showToast("Failed to load response: " + e);
                            return false;
                        }
                    });
                }
            });
        },

        /**
         * Trigger option to add link to webtrees Tree Favourites page, from saved setting context menu
         *
         * @param e
         */
        addUrlToTreeFavourites(e) {
            e.stopPropagation();
            let parent = event.target.parentElement;
            while (!parent.dataset.id) {
                parent = parent.parentElement;
            }
            let id = parent.getAttribute('data-id');
            isUserLoggedIn().then((loggedIn) => {
                if (loggedIn) {
                    let request = {
                        "type": REQUEST_TYPE_ADD_TREE_FAVORITE,
                        "settings_id": id
                    };
                    let json = JSON.stringify(request);
                    sendRequest(json).then((response) => {
                        try {
                            let json = JSON.parse(response);
                            if (json.success) {
                                UI.showToast(TRANSLATE['Added to Tree favourites']);
                            } else {
                                UI.showToast(ERROR_CHAR + json.errorMessage);
                            }
                        } catch (e) {
                            UI.showToast("Failed to load response: " + e);
                            return false;
                        }
                    });
                }
            });
        }
    },

    /**
     * Make a list draggable - to use, run the addDragHandlers() function on each item in the list
     */
    draggableList: {
        dragEl: null,

        /**
         * Adds event listeners to list item element - run this on each <li> element in the list to initiate dragging functionality
         *
         * @param el
         */
        addDragHandlers(el) {
            el.addEventListener('dragstart', UI.draggableList.handleDragStart, false);
            el.addEventListener('dragover', UI.draggableList.handleDragOver, false);
            el.addEventListener('dragleave', UI.draggableList.handleDragLeave, false);
            el.addEventListener('drop', UI.draggableList.handleDrop, false);
            el.addEventListener('dragend', UI.draggableList.handleDragEnd, false);
        },

        /**
         * When you start dragging
         *
         * @param e
         */
        handleDragStart(e) {
            UI.draggableList.dragEl = this;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        },

        /**
         * When you drag over a list item
         *
         * @param e
         * @returns {boolean}
         */
        handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            this.classList.add('over');
            e.dataTransfer.dropEffect = 'move';
            return false;
        },

        /**
         * Remove highlighting when dragging goes away from this list item
         */
        handleDragLeave() {
            this.classList.remove('over');
        },

        /**
         * Remove highlighting when dragging stops
         */
        handleDragEnd(e) {
            this.classList.remove('over');
        },

        /**
         * When you drop item you've been dragging
         *
         * @param e
         * @returns {boolean}
         */
        handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation(); // May help stop browser redirect
            }

            if (UI.draggableList.dragEl !== this) { // If you didn't just drop back where it was
                this.parentNode.removeChild(UI.draggableList.dragEl);
                const dropHTML = e.dataTransfer.getData('text/html');
                this.insertAdjacentHTML('beforebegin',dropHTML);
                const dropElem = this.previousSibling;
                UI.draggableList.addDragHandlers(dropElem);

            }
            this.classList.remove('over');
            return false;
        }
    }
};
