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
                        contentEl.innerHTML = UI.helpPanel.decodeHTML(response);
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

        decodeHTML(html) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
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
                            UI.savedSettings.addSettingsMenuOption(id, div, '🌟', 'Add to My favorites', UI.savedSettings.addUrlToMyFavouritesMenuAction);
                        }
                        if (TREE_FAVORITES_MODULE_ACTIVE) {
                            UI.savedSettings.addSettingsMenuOption(id, div, '🌲', 'Add to Tree favorites', UI.savedSettings.addUrlToTreeFavourites);
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
    }
};
