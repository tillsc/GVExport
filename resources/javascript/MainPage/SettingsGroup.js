export class SettingsGroup {
    constructor(id, modifierId, value, customCondition) {
        this.id = id;
        this.linkModifierElement(modifierId, value, customCondition);
    }

    linkModifierElement(modifierId, value, customCondition) {
        let element = document.querySelector('#' + modifierId);
        element.addEventListener('change', () => {this.runEventListener(modifierId, value, customCondition)});
    }

    runEventListener(modifierId, value, customCondition) {
        if (typeof customCondition === 'function') {
            if (!customCondition()) return;
        }
        Form.showHideMatchDropdown(modifierId, this.id, value)
    }
}