(function() {
    'use strict';

    var csInterface = new CSInterface();
    
    var elements = {
        baseSize: document.getElementById('baseSize'),
        ratio: document.getElementById('ratio'),
        customRatio: document.getElementById('customRatio'),
        customRatioGroup: document.getElementById('customRatioGroup'),
        scaleSteps: document.getElementById('scaleSteps'),
        stepsValue: document.getElementById('stepsValue'),
        stylePrefix: document.getElementById('stylePrefix'),
        scalePreview: document.getElementById('scalePreview'),
        generateBtn: document.getElementById('generateBtn'),
        refreshBtn: document.getElementById('refreshBtn'),
        statusMessage: document.getElementById('statusMessage')
    };

    function getSettings() {
        var ratio = elements.ratio.value === 'custom' 
            ? parseFloat(elements.customRatio.value) 
            : parseFloat(elements.ratio.value);
        
        return {
            baseSize: parseFloat(elements.baseSize.value),
            ratio: ratio,
            steps: parseInt(elements.scaleSteps.value),
            prefix: elements.stylePrefix.value || 'Heading'
        };
    }

    function calculateScale(baseSize, ratio, steps) {
        var scale = [];
        
        for (var i = steps - 1; i >= 0; i--) {
            var size = baseSize * Math.pow(ratio, i);
            size = Math.round(size * 100) / 100;
            scale.push(size);
        }
        
        scale.push(baseSize / ratio);
        
        return scale;
    }

    function renderPreview() {
        var settings = getSettings();
        var scale = calculateScale(settings.baseSize, settings.ratio, settings.steps);
        var html = '';
        
        scale.forEach(function(size, index) {
            var levelName;
            if (index < settings.steps) {
                levelName = settings.prefix + ' ' + (index + 1);
            } else {
                levelName = 'Small Text';
            }
            
            var displaySize = size.toFixed(2);
            var sampleStyle = 'font-size: ' + Math.min(size * 0.8, 18) + 'px;';
            
            html += '<div class="scale-item" data-size="' + size + '">';
            html += '<span class="name">' + levelName + '</span>';
            html += '<span class="sample" style="' + sampleStyle + '">Aa</span>';
            html += '<span class="size">' + displaySize + 'pt</span>';
            html += '</div>';
        });
        
        elements.scalePreview.innerHTML = html;
    }

    function showStatus(message, type) {
        elements.statusMessage.textContent = message;
        elements.statusMessage.className = 'status-message ' + type;
        
        setTimeout(function() {
            elements.statusMessage.className = 'status-message';
        }, 5000);
    }

    function createParagraphStyles() {
        var settings = getSettings();
        var scale = calculateScale(settings.baseSize, settings.ratio, settings.steps);
        
        var styleData = scale.map(function(size, index) {
            var name;
            if (index < settings.steps) {
                name = settings.prefix + ' ' + (index + 1);
            } else {
                name = 'Small Text';
            }
            return {
                name: name,
                size: size
            };
        });
        
        var scriptArgs = JSON.stringify({
            styles: styleData,
            ratio: settings.ratio,
            baseSize: settings.baseSize
        });
        
        elements.generateBtn.disabled = true;
        elements.generateBtn.innerHTML = '<span class="icon">⏳</span> Creating Styles...';
        
        csInterface.evalScript('createParagraphStyles(' + scriptArgs + ')', function(result) {
            elements.generateBtn.disabled = false;
            elements.generateBtn.innerHTML = '<span class="icon">✓</span> Create Paragraph Styles';
            
            if (result && result !== 'undefined') {
                var response;
                try {
                    response = JSON.parse(result);
                } catch (e) {
                    response = { success: false, message: result };
                }
                
                if (response.success) {
                    showStatus(response.message || 'Paragraph styles created successfully!', 'success');
                } else {
                    showStatus(response.message || 'Failed to create styles.', 'error');
                }
            } else {
                showStatus('Styles created! Check your Paragraph Styles panel.', 'success');
            }
        });
    }

    function init() {
        elements.ratio.addEventListener('change', function() {
            if (this.value === 'custom') {
                elements.customRatioGroup.style.display = 'block';
            } else {
                elements.customRatioGroup.style.display = 'none';
            }
            renderPreview();
        });
        
        elements.baseSize.addEventListener('input', renderPreview);
        elements.customRatio.addEventListener('input', renderPreview);
        elements.stylePrefix.addEventListener('input', renderPreview);
        
        elements.scaleSteps.addEventListener('input', function() {
            elements.stepsValue.textContent = this.value;
            renderPreview();
        });
        
        elements.generateBtn.addEventListener('click', createParagraphStyles);
        elements.refreshBtn.addEventListener('click', renderPreview);
        
        renderPreview();
        
        applyTheme();
    }

    function applyTheme() {
        var hostEnv = csInterface.getHostEnvironment();
        if (hostEnv && hostEnv.appSkinInfo) {
            var skinInfo = hostEnv.appSkinInfo;
            var bgColor = skinInfo.panelBackgroundColor.color;
            
            if (bgColor) {
                var brightness = (bgColor.red + bgColor.green + bgColor.blue) / 3;
                if (brightness > 127) {
                    document.body.classList.add('light-theme');
                }
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
