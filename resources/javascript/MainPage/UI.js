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
            UI.helpPanel.loadHelp('Home');
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
            UI.helpPanel.loadHelp(help);
            document.querySelector(".help-toggle").hidden = true;
            document.querySelector(".help-sidebar").hidden = false;
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
         * Shows the About GVExport page
         */
        loadHelpAbout() {
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
                Data.getHelp(help).then(function (response) {
                    if (response) {
                        document.getElementById('help-content').innerHTML = response;
                    }
                });
            }
        },

        clickInfoIcon(event) {
            event.stopPropagation();
            event.preventDefault();
            UI.helpPanel.showHelpSidebar(event.target.getAttribute('data-help'));
        }
    }
};
