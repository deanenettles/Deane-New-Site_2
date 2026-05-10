/**
 * CSInterface - Adobe CEP JavaScript Interface
 * Minimal implementation for InDesign extensions
 */

var CSInterface = function() {};

CSInterface.prototype.evalScript = function(script, callback) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.evalScript(script, callback);
        } else {
            console.log('CSInterface: Running outside CEP environment');
            console.log('Script:', script);
            if (callback) {
                callback(JSON.stringify({
                    success: true,
                    message: 'Mock response - running outside InDesign'
                }));
            }
        }
    } catch (e) {
        console.error('evalScript error:', e);
        if (callback) {
            callback(JSON.stringify({
                success: false,
                message: e.message
            }));
        }
    }
};

CSInterface.prototype.getHostEnvironment = function() {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            var hostEnv = __adobe_cep__.getHostEnvironment();
            return JSON.parse(hostEnv);
        }
    } catch (e) {
        console.error('getHostEnvironment error:', e);
    }
    return {
        appSkinInfo: {
            panelBackgroundColor: {
                color: { red: 50, green: 50, blue: 50 }
            }
        }
    };
};

CSInterface.prototype.addEventListener = function(type, listener, obj) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.addEventListener(type, listener, obj);
        }
    } catch (e) {
        console.error('addEventListener error:', e);
    }
};

CSInterface.prototype.removeEventListener = function(type, listener, obj) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.removeEventListener(type, listener, obj);
        }
    } catch (e) {
        console.error('removeEventListener error:', e);
    }
};

CSInterface.prototype.requestOpenExtension = function(extensionId) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.requestOpenExtension(extensionId);
        }
    } catch (e) {
        console.error('requestOpenExtension error:', e);
    }
};

CSInterface.prototype.closeExtension = function() {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.closeExtension();
        }
    } catch (e) {
        console.error('closeExtension error:', e);
    }
};

CSInterface.prototype.getSystemPath = function(pathType) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            return __adobe_cep__.getSystemPath(pathType);
        }
    } catch (e) {
        console.error('getSystemPath error:', e);
    }
    return '';
};

var SystemPath = {
    USER_DATA: 'userData',
    COMMON_FILES: 'commonFiles',
    MY_DOCUMENTS: 'myDocuments',
    APPLICATION: 'application',
    EXTENSION: 'extension',
    HOST_APPLICATION: 'hostApplication'
};

CSInterface.prototype.openURLInDefaultBrowser = function(url) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.openURLInDefaultBrowser(url);
        } else {
            window.open(url, '_blank');
        }
    } catch (e) {
        console.error('openURLInDefaultBrowser error:', e);
    }
};

CSInterface.prototype.setContextMenu = function(menu, callback) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.setContextMenu(menu, callback);
        }
    } catch (e) {
        console.error('setContextMenu error:', e);
    }
};

CSInterface.prototype.setContextMenuByJSON = function(menuJson, callback) {
    try {
        if (typeof __adobe_cep__ !== 'undefined') {
            __adobe_cep__.setContextMenuByJSON(menuJson, callback);
        }
    } catch (e) {
        console.error('setContextMenuByJSON error:', e);
    }
};
