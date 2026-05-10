/**
 * RatioApp - InDesign Paragraph Style Generator
 * ExtendScript (JSX) for Adobe InDesign
 */

function createParagraphStyles(jsonData) {
    try {
        var data = JSON.parse(jsonData);
        var styles = data.styles;
        var doc = app.activeDocument;
        
        if (!doc) {
            return JSON.stringify({
                success: false,
                message: "No active document. Please open or create a document first."
            });
        }
        
        var createdCount = 0;
        var updatedCount = 0;
        var groupName = "RatioApp Scale";
        var styleGroup;
        
        try {
            styleGroup = doc.paragraphStyleGroups.itemByName(groupName);
            if (!styleGroup.isValid) {
                styleGroup = doc.paragraphStyleGroups.add({name: groupName});
            }
        } catch (e) {
            styleGroup = doc.paragraphStyleGroups.add({name: groupName});
        }
        
        for (var i = 0; i < styles.length; i++) {
            var styleInfo = styles[i];
            var styleName = styleInfo.name;
            var fontSize = styleInfo.size;
            
            var paragraphStyle;
            var isExisting = false;
            
            try {
                paragraphStyle = styleGroup.paragraphStyles.itemByName(styleName);
                if (paragraphStyle.isValid) {
                    isExisting = true;
                }
            } catch (e) {
                isExisting = false;
            }
            
            if (isExisting) {
                paragraphStyle.pointSize = fontSize;
                paragraphStyle.leading = fontSize * 1.4;
                updatedCount++;
            } else {
                var newStyle = styleGroup.paragraphStyles.add({
                    name: styleName,
                    pointSize: fontSize,
                    leading: fontSize * 1.4,
                    appliedFont: app.fonts.item(0),
                    justification: Justification.LEFT_ALIGN,
                    hyphenation: false
                });
                
                if (i === 0) {
                    newStyle.fontStyle = "Bold";
                    newStyle.pointSize = fontSize;
                } else if (i === 1) {
                    newStyle.fontStyle = "Bold";
                } else if (i === 2) {
                    newStyle.fontStyle = "Semibold";
                }
                
                var trackingValue = 0;
                if (fontSize > 24) {
                    trackingValue = -10;
                } else if (fontSize > 18) {
                    trackingValue = -5;
                }
                newStyle.tracking = trackingValue;
                
                createdCount++;
            }
        }
        
        var message = "";
        if (createdCount > 0) {
            message += "Created " + createdCount + " new style(s). ";
        }
        if (updatedCount > 0) {
            message += "Updated " + updatedCount + " existing style(s). ";
        }
        message += "Check '" + groupName + "' in Paragraph Styles.";
        
        return JSON.stringify({
            success: true,
            message: message,
            created: createdCount,
            updated: updatedCount
        });
        
    } catch (e) {
        return JSON.stringify({
            success: false,
            message: "Error: " + e.message
        });
    }
}

function getDocumentInfo() {
    try {
        if (app.documents.length === 0) {
            return JSON.stringify({
                hasDocument: false,
                message: "No document open"
            });
        }
        
        var doc = app.activeDocument;
        return JSON.stringify({
            hasDocument: true,
            name: doc.name,
            pages: doc.pages.length,
            paragraphStyles: doc.paragraphStyles.length
        });
    } catch (e) {
        return JSON.stringify({
            hasDocument: false,
            message: e.message
        });
    }
}

function listExistingStyles() {
    try {
        if (app.documents.length === 0) {
            return JSON.stringify({
                success: false,
                styles: []
            });
        }
        
        var doc = app.activeDocument;
        var styleNames = [];
        
        for (var i = 0; i < doc.paragraphStyles.length; i++) {
            styleNames.push({
                name: doc.paragraphStyles[i].name,
                size: doc.paragraphStyles[i].pointSize
            });
        }
        
        return JSON.stringify({
            success: true,
            styles: styleNames
        });
    } catch (e) {
        return JSON.stringify({
            success: false,
            message: e.message,
            styles: []
        });
    }
}

function applyStyleToSelection(styleName, groupName) {
    try {
        var doc = app.activeDocument;
        var sel = app.selection;
        
        if (!sel || sel.length === 0) {
            return JSON.stringify({
                success: false,
                message: "Nothing selected"
            });
        }
        
        var styleGroup = doc.paragraphStyleGroups.itemByName(groupName || "RatioApp Scale");
        var style = styleGroup.paragraphStyles.itemByName(styleName);
        
        if (!style.isValid) {
            return JSON.stringify({
                success: false,
                message: "Style not found: " + styleName
            });
        }
        
        for (var i = 0; i < sel.length; i++) {
            if (sel[i].hasOwnProperty('appliedParagraphStyle')) {
                sel[i].appliedParagraphStyle = style;
            }
        }
        
        return JSON.stringify({
            success: true,
            message: "Applied style: " + styleName
        });
    } catch (e) {
        return JSON.stringify({
            success: false,
            message: e.message
        });
    }
}
