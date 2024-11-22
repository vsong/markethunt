/**
 * Generate a true random 48bit number represented as a 12 character hex string
 * @returns Cryptographically random 12 character hex string
 */
function uid() {
    return '000000000000'.replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

function formatPercent(num, decimalDigits = 1) {
    if (num > 0) {
        return '+' + num.toFixed(decimalDigits) + '%';
    } else if (num < 0) {
        return num.toFixed(decimalDigits) + '%';
    } else {
        return (0).toFixed(decimalDigits) + '%';
    }
}

function getVueColorClassBinding(num) {
    if (num > 0) {
        return {'gains-text' : true};
    } else if (num < 0) {
        return {'loss-text' : true};
    } else {
        return {};
    }
}

function getMarkTypeLocaleStringOpts(markType) {
    const stringOpts = {
        'gold': [undefined, {maximumFractionDigits: 0, minimumFractionDigits: 0}],
        'sb': [undefined, {maximumFractionDigits: 2, minimumFractionDigits: 2}]
    };

    if (markType == null) {
        return stringOpts.gold;
    }

    return stringOpts[markType];
}

function getMarkTypeAppendText(markType) {
    const appendTextLookup = {
        'gold': '',
        'sb': ' SB'
    };

    if (markType == null) {
        return '';
    }

    return appendTextLookup[markType];
}

function isDebugModeEnabled() {
    return !!(debugMode) && localStorage.enableDebug === 'true';
}

function unixTimeToIsoString(timestamp) {
    const date = new Date(timestamp);
    return date.toISOString().split('T')[0];
}