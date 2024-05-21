/**
 * Data object to hold form related functionality
 *
 * @type {{}}
 */
const Form = {
    /**
     * Add or remove the % sign from the text input
     *
     * @param element
     * @param add
     */
    togglePercent(element, add) {
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

    /**
     * Update provided element with provided value when element blank
     *
     * @param element
     * @param value
     */
    setDefaultValueIfBlank(element, value) {
        if (element.value === "") {
            element.value = value;
        }
    },

    /**
     * Checks if a starting individual is selected or the list is blank
     *
     * @returns {boolean}
     */
    isIndiBlank() {
        let el = document.getElementsByClassName("item");
        let list = document.getElementById('xref_list');
        return el.length === 0 && list.value.toString().length === 0;
    },
    /**
     * This function ensures that if certain options are checked in regard to which relations to include,
     * then other required options are selected. e.g. if "Anyone" is selected, all other options are
     * set to selected.
     *
     * @param field
     */
    updateRelationOption(field) {
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
    /**
     * Gets position of element relative to another
     * From https://stackoverflow.com/questions/1769584/get-position-of-element-by-javascript
     *
     * @param el
     * @param rel
     * @returns {{x: number, y: number}}
     */
    getPos(el, rel)
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

    clearSelect(selectId) {
        let dropdown = document.getElementById(selectId);
        if (typeof dropdown.tomselect !== 'undefined') {
            dropdown.tomselect.clear();
        } else {
            setTimeout(function () {
                Form.clearSelect(selectId);
            }, 100);
        }
    },

    /**
     * Toggle items based on if the items in the cart should be used or not
     * enable - if set to true, use cart. Update form to disable options. Set to "false" to reverse.
     *
     * @param enable
     */
    toggleCart(enable) {
            const el = document.getElementsByClassName("cart_toggle");
            for (let i = 0; i < el.length; i++) {
                el.item(i).disabled = enable;
            }
            Form.showHideClass("cart_toggle_hide", !enable);
            Form.showHideClass("cart_toggle_show", enable);
        },

    /**
     * This function is used in Form.toggleCart to show or hide all elements with a certain class,
     * by adding or removing "display: none"
     *
     * @param css_class the class to search for
     * @param show true to show the elements and false to hide them
     */
    showHideClass(css_class, show) {
        let el = document.getElementsByClassName(css_class);
        for (let i = 0; i < el.length; i++) {
            Form.showHide(el.item(i), show)
        }
    },
    /**
     * Show or hide an element on the page
     *
     * @param element
     * @param show whether to show (true) or hide (false) the element
     */
    showHide(element, show) {
        if (show) {
            element.style.removeProperty("display");
        } else {
            element.style.display = "none";
        }
    },

    /**
     * Show or hide an element based on whether a checkbox is checked
     *
     * @param checkboxId
     * @param elementId
     */
    showHideMatchCheckbox(checkboxId, elementId) {
        Form.showHide(document.getElementById(elementId), document.getElementById(checkboxId).checked);
    },

    /**
     * Show or hide an element based on whether a select field is a certain value
     *
     * @param dropdownId
     * @param elementId element to show/hide
     * @param value
     */
    showHideMatchDropdown(dropdownId, elementId, value) {
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

    /**
     * Show or hide a settings group based on toggle arrow
     *
     * @param elementId
     * @param callingEl
     */
    showHideSubgroup(elementId, callingEl) {
        let callerText = callingEl.innerText;
        let visible = callerText.includes('↓');
        Form.showHide(document.getElementById(elementId), !visible);
        if (visible) {
            callingEl.innerText = callerText.replace('↓', '→');
        } else {
            callingEl.innerText = callerText.replace('→', '↓');
        }

    },

    /**
     * Shows or hides the diagram search box
     *
     * @param event
     * @param visible (optional) whether to show (true) or hide, leave blank to toggle
     */
    showHideSearchBox(event, visible = null) {
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

    /**
     * Toggle the showing of an advanced settings section
     *
     * @param button the button element calling the script
     * @param id the id of the element we are toggling
     * @param visible whether to make element visible or hidden. Null to toggle current state.
     */
    toggleAdvanced(button, id, visible = null) {
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
    },

    /**
     * Shared note panel - form displaying options that allow styling individuals based on their linked shared notes
     */
    sharedNotePanel: {

        /**
         * Run startup code
         */
        init() {
            document.getElementById('shared_note_button').addEventListener('click', Form.sharedNotePanel.clickSharedNoteButton);
            document.getElementById('sharednote_col_add').addEventListener('change', Form.sharedNotePanel.noteSelectChanged);
            document.getElementById('sharednote_col_default').addEventListener('change', Form.sharedNotePanel.defaultChanged);
        },

        /**
         * Handle event when button to show shared note panel is clicked
         */
        clickSharedNoteButton() {
            Form.sharedNotePanel.showNoteModal();
        },

        /**
         * Runs when user changes shared note selection box (i.e. chooses shared note to add)
         */
        noteSelectChanged() {
            let xref = document.getElementById('sharednote_col_add').value.trim();
            if (xref !== "") {
                Form.sharedNotePanel.addNoteToList(xref);
            }
        },

        /**
         * Adds the provided note XREF to the list of shared notes
         *
         * @param xref
         */
        addNoteToList(xref) {
            const obj = Form.sharedNotePanel.getNoteListJSON();
            const xrefTrim = xref.replaceAll('@','');
            if (!obj.find(item => item.xref === xrefTrim)) {
                const newNote = {
                    xref: xrefTrim,
                    bg_col: shared_note_default
                };
                obj.push(newNote);
            }
            document.getElementById('sharednote_col_data').value = JSON.stringify(obj);
            Form.clearSelect('sharednote_col_add');
            Form.sharedNotePanel.showNoteModal();
        },

        /**
         * Displays the modal for managing shared note settings
         *
         * @returns {Promise<*>}
         */
        showNoteModal() {
            return Data.getSharedNoteForm().then(function (response) {
                if (response) {
                    showModal(Data.decodeHTML(response));
                    const items = document.querySelectorAll('#shared_note_list .sharednote-list-item');
                    [].forEach.call(items, UI.draggableList.addDragHandlers);
                    // save to update count in case user backs out without saving as note is already added
                    Form.sharedNotePanel.saveSharedNotesData(false);
                } else {
                    setTimeout(function(){location.reload();}, 3000);
                    UI.showToast(ERROR_CHAR + TRANSLATE['Login expired. Reloading page...']);
                }
            });
        },

        /**
         * Returns the JSON object stored for the shared note list
         *
         * @returns {any}
         */
        getNoteListJSON() {
            let list = document.getElementById('sharednote_col_data');
            return JSON.parse(list.value || '[]');
        },

        /**
         * Triggered when user clicks on the save button of the shared notes modal
         */
        saveButtonClick() {
            Form.sharedNotePanel.saveSharedNotesData(true);
            document.getElementById('modal').remove();
        },

        /**
         * Saves the data from the shared notes modal into JSON and stored in hidden field
         *
         * @param update {boolean} Whether to update the diagram (only if auto-update enabled)
         */
        saveSharedNotesData(update) {
            const listItems = document.querySelectorAll('.sharednote-list-item');
            const outputJSON = [];
            listItems.forEach(item => {
                const picker = item.querySelector('.picker');
                // Only add items that have settings in them - not the blank end one
                if (picker !== null) {
                    const xref = item.getAttribute('data-xref');
                    const bgColour = item.querySelector('.picker').value;
                    const itemObject = {
                        "xref": xref,
                        "bg_col": bgColour
                    };
                    outputJSON.push(itemObject);
                }
            });
            document.getElementById('sharednote_col_data').value = JSON.stringify(outputJSON);

            // Update count on form
            const el = document.getElementById('sharednote-count');
            let itemCount = outputJSON.length;
            if (itemCount === 0) {
                el.innerHTML = TRANSLATE['No styles set based on shared notes.'];
            } else {
                el.innerHTML = TRANSLATE['%s shared note styles saved.'].replace('%s', itemCount);
            }
            // If auto-update of diagram is enabled, trigger it
            if (autoUpdate && update) updateRender();
        },
        defaultChanged() {
            shared_note_default = this.value;
        }
    },

    /**
     * List of individuals for building diagram
     */
    indiList: {
        /**
         * Clear the list of starting individuals
         *
         * @param update Whether to update diagram (only if auto-update enables)
         */
        clearIndiList(update = true) {
            document.getElementById('xref_list').value = "";
            document.getElementById('indi_list').innerHTML = "";
            updateClearAll();
            if (autoUpdate && update) updateRender();
        },

        /**
         * Add an individual to the list of starting individuals
         *
         * @param xref ID of the individual to add
         */
        addIndiToList(xref) {
            let list = document.getElementById('xref_list');
            const regex = new RegExp(`(?<=,|^)(${xref})(?=,|$)`);
            if (!regex.test(list.value.replaceAll(" ','"))) {
                appendXrefToList(xref, 'xref_list');
                Form.indiList.loadIndividualDetails(TOMSELECT_URL, xref, 'indi_list').then(() => {
                    toggleHighlightStartPersons(document.getElementById('highlight_start_indis').checked);
                })
            }
            Form.clearSelect('pid');
        },

        /**
         * Triggered when the selection box for selecting a starting individual is changed
         */
        indiSelectChanged() {
            let xref = document.getElementById('pid').value.trim();
            if (xref !== "") {
                Form.indiList.addIndiToList(xref);
                mainPage.Url.changeURLXref(xref);
                if (autoUpdate) {
                    updateRender();
                }
            }
        },

        /**
         * Updates the list of starting individuals to add the details of the individual
         *
         * @param url The webtrees URL that runs the Tom-select search that we use to pull the details
         * @param xref The webtrees ID of the individual
         * @param list 'indi_list' if it's the starting individual list, otherwise it updates the stopping individual's list
         * @returns {Promise<void>}
         */
        loadIndividualDetails(url, xref, list) {
            return fetch(url + xref.trim()).then(async (response) => {
                const data = await response.json();
                let contents;
                let otherXrefId;
                if (list === "indi_list") {
                    otherXrefId = "xref_list";
                } else {
                    otherXrefId = "stop_xref_list";
                }
                if (data["data"].length !== 0) {
                    for (let i=0; i< data['data'].length; i++) {
                        if (xref.toUpperCase() === data['data'][i].value.toUpperCase()) {
                            contents = data["data"][i]["text"];
                            // Fix case if mismatched
                            if (xref !== data['data'][i].value) {
                                let listEl = document.getElementById(otherXrefId);
                                let indiList = listEl.value.split(',');
                                for (let j = indiList.length-1; j>=0; j--) {
                                    if (indiList[j].trim() === xref.trim()) {
                                        indiList[j] = data["data"][i].value;
                                        break;
                                    }
                                }
                                listEl.value = indiList.join(',');
                                setTimeout(()=>{refreshIndisFromXREFS(false)}, 100);
                                handleFormChange();
                            }
                        }
                    }
                } else {
                    contents = xref;
                }
                const listElement = document.getElementById(list);
                const newListItem = document.createElement("div");
                newListItem.className = "indi_list_item";
                newListItem.setAttribute("data-xref", xref);
                newListItem.setAttribute("onclick", "UI.scrollToRecord('"+xref+"')");
                newListItem.innerHTML = contents + "<div class=\"saved-settings-ellipsis\" onclick=\"removeItem(event, this.parentElement, '" + otherXrefId + "')\"><a class='pointer'>×</a></div>";
                // Multiple promises can be for the same xref - don't add if a duplicate
                let item = listElement.querySelector(`[data-xref="${xref}"]`);
                if (item == null) {
                    listElement.appendChild(newListItem);
                } else {
                    newListItem.remove();
                }
                updateClearAll();
            })
        }
    }
}
