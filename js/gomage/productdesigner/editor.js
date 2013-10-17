/**
 * EcmaScript 5 compatibility
 */
if (!Function.prototype.bind) {
    Function.prototype.bind = function (oThis) {
        if (typeof this !== 'function') throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
        var aArgs = Array.prototype.slice.call(arguments, 1);
        var fToBind = this;
        var fNOP = function () {};
        var fBound = function () { return fToBind.apply(this instanceof fNOP && oThis ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments))); };
        fNOP.prototype = this.prototype;
        fBound.prototype = new fNOP();
        return fBound;
    };
}

/**
 * Convert first char in string to upper case
 */
var $ucfirst = function(str) {
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
};
var $merge = function(object1, object2) {
    if(typeof object1 !== 'array' && typeof object1 !== 'object') {
        return;
    }
    if(typeof object2 !== 'array' && typeof object2 !== 'object') {
        return;
    }
    for(var attribute in object2) {
        object1[attribute] = object2[attribute];
    }
    return object1;
}

function getUrlParams() {
    var paramString = window.location.search.substr(1);
    var paramArray = paramString.split ("&");
    var params = {};

    for(var i=0; i < paramArray.length; i++) {
        var tmpArray = paramArray[i].split("=");
        params[tmpArray[0]] = tmpArray[1];
    }
    return params;
}

$c = function(name, attrs, css) {
        var attrs = attrs || {};
        var css = css || {};
        var el = document.createElement(name);

        for (var k in attrs) {
            if (attrs.hasOwnProperty(k)) {
                el.setAttribute(k, attrs[k]);
            }
        }

        for (var k in css) {
            if (css.hasOwnProperty(k)) {
                el.style[k] = css[k];
            }
        }

        return el;
    };
/**
 * Create module namespace if not defined
 */
if (typeof GoMage == 'undefined') GoMage = {};

GoMage.ProductDesigner = function(config, continueUrl, loginUrl, registrationUrl, saveDesign) {
    'use strict';
    this.opt = {
        product_side_id: 'pd_sides',
        canvasScale: 1,
        scale_factor: 1.2
    };
    this.urls = {
        continue: continueUrl,
        login: loginUrl,
        registration: registrationUrl,
        saveDesign: saveDesign
    }
    this.history = new History();
    this.layersManager = new LayersManager(this);
    this.config = config;
    this.prices = config.prices;
    this.container = config.container;
    this.navigation = config.navigation;
    this.currentProd = null;
    this.containerLayers = {},
    this.containerCanvases = {},
    this.productAdditionalImageTemplate = new Template($('product-image-template').innerHTML);
    this.isCustomerLogin = this.config.isCustomerLoggedIn;
    this.currentColor = null;
    
    this.loadProduct(config.product);
    this.observeLayerControls();
    this.observeTabs();
    this.observeSaveDesign();
    this.observeContinueBtn();
    this.observeProductImageChange();
    this.observeProductImageColorChange();
    this.observeCanvasObjectModified();
    this.observeCanvasObjectMoving();
    this.observeCanvasObjectSelected();
    this.observeCanvasObjectRendered();
    this.initPrices();
    this.reloadPrice();
    this.observePriceMoreInfo();
}

GoMage.ProductDesigner.prototype = {

    loadProduct : function(product, color) {
        if (!product) {
            return;
        }
        if (!color) {
            color = product.default_color;
        }
        if (!product.images.hasOwnProperty(color)) {
            return;
        }

        this.currentColor = color;
        var images = product.images[color];

        for (var prop in images) {
            if (images.hasOwnProperty(prop)) {
                var img = images[prop];
                this.addDesignArea(img);
                return;
            }
        }
    },

    changeProduct: function(data){
        var product = data.product_settings;
        var price = data.design_price;
        var colors = data.product_colors;

        this.layersManager.clear();
        this.history.clear();
        this.containerLayers = {};
        this.containerCanvases = {};
        this.container.innerHTML = '';
        this.config.product = product;
        this.loadProduct(product);
        this.updateProductImages(product);
        this.updateProductColors(colors);
        this.showTabsSwitchers();
        this.showControls();
        this.showAdditionalPannel();
        this.config.isProductSelected = true;

        if (price) {
            if (!$('design_price_container').visible()) {
                $('design_price_container').show();
            }
            $('design_price_container').update(price);
            if (optionsPrice != undefined && data.price_config) {
                optionsPrice.initialize(data.price_config);
            }
            this.initPrices();
            this.reloadPrice();
        }
    },

    changeProductImage : function(id) {
        var img = this.config.product.images[this.currentColor][id];
        if(img && this.currentProd != img.id) {
            this.containerCanvases[this.currentProd] = this.canvas;
            this.containerLayers[this.currentProd] = this.container.childElements()[0].remove();

            this.history.clear();
            this.addDesignArea(img);
        }
    },

    changeProductColor: function(color){
        this.layersManager.clear();
        this.history.clear();
        this.containerLayers = {};
        this.containerCanvases = {};
        this.container.innerHTML = '';
        var product = this.config.product;
        this.loadProduct(product, color);
        this.updateProductImages(product);
        this.reloadPrice();
    },

    updateProductImages: function(product) {
        if (!$(this.opt.product_side_id)) {
            return;
        }
        var productsList = $(this.opt.product_side_id).down('ul');
        productsList.innerHTML = '';
        var images = product.images[this.currentColor];
        var imageTemplateData = {};
        var imagesHtml = '';
        for (var id in images) {
            if (images.hasOwnProperty(id)) {
                imageTemplateData['url'] = images[id].u;
                imageTemplateData['image-id'] = id;
                imageTemplateData['data-image-id'] = images[id].id;
                imagesHtml += this.productAdditionalImageTemplate.evaluate(imageTemplateData);
            }
        }
        productsList.innerHTML = imagesHtml;
    },

    updateProductColors: function(colors){
        $('product-colors').innerHTML = '';
        if (colors) {
            var colorsHtml = '';
            for (var id in colors) {
                if (colors.hasOwnProperty(id)) {
                    var color = colors[id];
                    if (!this.config.product.images.hasOwnProperty(color['option_id'])) {
                        continue;
                    }
                    var element = document.createElement('span');
                    element.addClassName('color-btn');
                    element.setAttribute('color_id', color['option_id']);
                    if (color['image']) {
                        element.setStyle({
                            'background-image': 'url('+ color['image'] +')',
                            'background-position': '50% 50%',
                            'background-repeat': 'no-repeat no-repeat'
                        });
                    } else {
                        element.innerHTML = color['value'];
                    }

                    if (this.currentColor == color['option_id']) {
                        element.addClassName('selected');
                    }
                    colorsHtml += element.outerHTML;
                }
            }
            $('product-colors').innerHTML = colorsHtml;
            if (!$('product-colors').visible() && colorsHtml != '') {
                $('product-colors').show();
            }
        }
    },

    showTabsSwitchers: function(){
        if (!this.config.isProductSelected) {
            if (this.config.isDesignedEnabled) {
                $(this.config.navigation.addDesign.show());
            }
            if (this.config.isTextEnabled) {
                $(this.config.navigation.addText.show());
            }
            if (this.config.isUploadImageEnabled) {
                $(this.config.navigation.addImage.show());
            }
        }
    },

    showControls: function(){
        if (!this.config.isProductSelected) {
            $('pd_save_container').show();
            $('pd_bottom_panel').show();
        }
    },

    showAdditionalPannel: function(){
        if (!this.config.isProductSelected) {
            $('additional_panels').show();
        }
    },

    observeTabs: function(){
        $('pd_nav_container').childElements().invoke('observe', 'click', function(e){
            var buttonId = e.target.id || e.srcElement.id;
            var tabContentElement = $(buttonId+'-content');
            if(tabContentElement) {
                if (buttonId == 'pd_add_text') {
                    var event = document.createEvent('Event');
                    event.initEvent('addTextTabShow', true, true);
                    document.dispatchEvent(event);
                }
                tabContentElement.siblings().invoke('setStyle', {display:'none'});
                if(tabContentElement.getStyle('display') == 'none') {
                    tabContentElement.setStyle({display:'block'});
                }
            }
        }.bind(this));
    },

    addDesignArea : function(prod) {
        if(typeof prod === 'undefined') {
            return;
        }
        this.container.style.height = parseInt(prod.d[1]) + 'px';
        this.container.style.width = parseInt(prod.d[0]) + 'px';
        this.container.style.background = 'url(' + prod.u +') no-repeat center';

        if(typeof this.containerLayers[prod.id] === 'undefined') {
            var designArea = $c('div');
            designArea.setAttribute('class', 'pd-design-area');
            designArea.setAttribute('id', 'designArea-'+prod.id);
            designArea.style.position   = 'absolute';
            designArea.style.marginLeft = this.calculateOffsetByX(prod) + 'px';
            designArea.style.marginTop  = this.calculateOffsetByY(prod) + 'px';
            designArea.style.zIndex     = '1000';

            var canvas = $c('canvas');
            canvas.setAttribute('class', 'pd-canvas-pane');
            canvas.setAttribute('width', prod.w);
            canvas.setAttribute('height', prod.h);

            designArea.appendChild(canvas);

            this.container.appendChild(designArea);
            this.designArea = designArea;                
            this.canvas = new fabric.Canvas(canvas);
            this.canvas.selection = false;
            this.containerCanvases[prod.id] = this.canvas;
        } else {
            var designArea = this.containerLayers[prod.id];
            this.container.appendChild(designArea);
            this.designArea = designArea;
            this.canvas = this.containerCanvases[prod.id];
            this.canvas.selection = false;
        }
        this.currentProd = prod.id;
        var event = document.createEvent('Event');
        event.initEvent('addTextTabShow', true, true);
        document.dispatchEvent(event);
    },

    calculateOffsetByY : function(prod) {
        return parseInt(prod.t) - Math.round(parseInt(prod.h) / 2);
    },

    calculateOffsetByX : function (prod) {
        var x1 = Math.round(this.container.getWidth() / 2);
        var x2 = Math.round(prod.d[0] / 2);
        return parseInt(x1 - x2) + parseInt(prod.l) - Math.round(prod.w / 2);
    },

    observeSaveDesign: function() {
        if(this.navigation.saveDesign) {
            this.navigation.saveDesign.observe('click', function(e) {
                e.stop();
                if (!this.canvasesHasLayers()) {
                    alert('Please add to canvas thogh one layer');
                    return;
                }
                if (!this.isCustomerLogin) {
                    this.createCustomerLoginWindows();
                    if (this.loginWindow) {
                        this.loginWindow.showCenter(true);
                    }
                } else if(this.isCustomerLogin) {
                    this.saveDesign(this.urls.saveDesign, this.saveDesignCallback);
                }
            }.bind(this));
        }
    },

    createCustomerLoginWindows: function() {
        if(!this.isCustomerLogin) {
            if ($('customer-login-container')){
                this.loginWindow = this.createPopupWindow('customer-login-container', 'login-error-msg', {
                    className: 'magento',
                    title: 'Login',
                    width: 430,
                    minWidth: 430,
                    maximizable:false,
                    minimizable:false,
                    resizable:false,
                    draggable:false,
                    showEffect:Effect.BlindDown,
                    hideEffect:Effect.BlindUp,
                    showEffectOptions: {duration: 0.4},
                    hideEffectOptions: {duration: 0.4}
                });

                this.observeRegisterBtn();
                this.observeLogin();
            }
            if ($('customer-register-container')) {
                this.registrationWindow = this.createPopupWindow('customer-register-container', 'register-error-msg', {
                    className: 'magento',
                    title: 'Registration',
                    width: 900,
                    minWidth: 900,
                    minHeight: 410,
                    maximizable:false,
                    minimizable:false,
                    resizable:false,
                    draggable:false,
                    showEffect:Effect.BlindDown,
                    hideEffect:Effect.BlindUp,
                    showEffectOptions: {duration: 0.4},
                    hideEffectOptions: {duration: 0.4}
                });
                this.observeRegisterSubmitBtn();
            }
        }
    },

    createPopupWindow: function(contentId, errorContainerId, params){
        var win = new Window(params);
        win.errorContainerId = errorContainerId
        win.setContent(contentId, true, true);
        win.setZIndex(2000);
        return win;
    },

    observeRegisterBtn: function() {
        var registerBtn = $('customer-register-btn');
        if (registerBtn){
            registerBtn.observe('click', function(e){
                e.stop();
                this.loginWindow.close();
                setTimeout(function(){
                    this.registrationWindow.showCenter(true);
                }.bind(this), 700);
            }.bind(this));
        }
    },

    observeRegisterSubmitBtn: function() {
        var registerBtn = $('customer-register-submit-btn');
        if (registerBtn) {
            registerBtn.observe('click', function(e) {
                e.stop();
                this.loginAndSaveDesign(this.urls.registration, 'form-validate', this.registrationWindow);
            }.bind(this));
        }
    },

    observeLogin: function() {
        var loginBtn = $('customer-login-btn');
        if (loginBtn) {
            loginBtn.observe('click', function(e){
                e.stop();
                this.loginAndSaveDesign(this.urls.login, 'login-form', this.loginWindow);
            }.bind(this));
        }
    },

    loginAndSaveDesign: function(url, formId, window) {
        var form = new VarienForm(formId, true);
        if (form.validator.validate()) {
            var elements = form.form.elements;
            var data = {};
            for (var index in elements) {
                if (elements.hasOwnProperty(index)) {
                    var elm = elements[index];
                    if (typeof elm == "object" && elm.tagName == 'INPUT') {
                        if (elm.type == 'checkbox') {
                            data[elm.name] = elm.checked
                        } else {
                            data[elm.name] = elm.value;
                        }
                    }
                }
            };
            var imagesData = this.prepareImagesForSave();
            var data = Object.extend(data, imagesData);
            new Ajax.Request(url, {
                method: 'post',
                parameters: data,
                onSuccess: function(transport) {
                    var response = transport.responseText.evalJSON();
                    if (response.status == 'success') {
                        this.isCustomerLogin = true;
                        this.clearLoginErrors(window.errorContainerId);
                        var quickAccessContainer = $$('.quick-access')[0];
                        if (quickAccessContainer && quickAccessContainer != undefined) {
                            if (response.welcome_text) {
                                var welcomeText = quickAccessContainer.down('.welcome-msg');
                                welcomeText.update(response.welcome_text);
                            }
                            if (response.top_links) {
                                var topLinks = quickAccessContainer.down('.links');
                                topLinks.replace(response.top_links);
                            }
                        }
                        window.close();
                        alert('Design was saved');
                    } else if (response.status == 'redirect' && response.url) {
                        location.href = response.url;
                    } else if (response.status == 'error' && response.message) {
                        if (typeof response.message == 'object') {
                            response.message.each(function(message){
                                this.addLoginError(window.errorContainerId, message);
                            }.bind(this));
                        } else {
                            this.addLoginError(window.errorContainerId, response.message);
                        }

                    }
                }.bind(this)
            });
        }
    },

    addLoginError: function(containerId, message) {
        var errorContainer = $(containerId);
        var item = document.createElement('span');
        item.innerHTML = message;
        var itemWrapper = document.createElement('li');
        itemWrapper.appendChild(item);
        errorContainer.down().appendChild(itemWrapper);
        errorContainer.up().show();
    },

    clearLoginErrors: function(containerId) {
        var errorContainer = $(containerId).down();
        errorContainer.innserHTML = '';
        errorContainer.up().hide();
    },

    prepareImagesForSave: function() {
        var data = {};

        if ((this.canvas == null) || this.canvas == 'undefined') {
            return data;
        }
        var images = {};
        for (var imageId in this.containerCanvases) {
            if (this.containerCanvases.hasOwnProperty(imageId)) {
                var canvas = this.containerCanvases[imageId];
                if (canvas.getObjects().length > 0) {
                    canvas.deactivateAll();
                    canvas.renderAll();
                    var image = canvas.toDataURL();
                    image = image.substr(image.indexOf(',') + 1).toString();
                    images[imageId] = image;
                    var contextTop = canvas.contextTop;
                    if (contextTop && contextTop != undefined) {
                        canvas.clearContext(contextTop);
                    }
                }
            }
        }
        if (Object.keys(images).length > 0) {
            var params = getUrlParams();
            data['id'] = params['id'];
            data['images'] = Object.toJSON(images);
            data['prices'] = Object.toJSON(this.designPrices);
            if (this.currentColor) {
                data['color'] = this.currentColor;
            }
        }

        return data;
    },

    canvasesHasLayers: function()
    {
        var count = 0;
        for (var imageId in this.containerCanvases) {
            var canvasCount = this.canvasHasLayers(imageId);
            if (canvasCount) {
                count += canvasCount;
            }
        }

        return count > 0 ? true : false;
    },

    canvasHasLayers: function(id){
        var canvas = this.containerCanvases[id];
        if (canvas && canvas != 'undefined') {
            return canvas.getObjects().length;
        }

        return false;
    },

    saveDesign: function(url, responseCallback){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }

        var params = this.prepareImagesForSave();
        new Ajax.Request(url, {
            method:'post',
            parameters: params,
            onSuccess: responseCallback.bind(this)
        });
    },

    continueCallback: function(transport){
        var response = transport.responseText.evalJSON();
        if (response.status == 'redirect' && response.url != undefined) {
            location.href = response.url;
        } else if (response.status == 'error') {
            console.log(response.message);
        }
    },

    saveDesignCallback: function(transport){
        var response = transport.responseText.evalJSON();
        if (response.status == 'success') {
            alert('Design was saved');
        } else if (response.status == 'error') {
            console.log(response.message);
        }
    },

    observeContinueBtn: function() {
        if(this.navigation.continue) {
            this.navigation.continue.observe('click', function(e) {
                e.stop();
                if (!this.canvasesHasLayers()) {
                    alert('Please add to canvas thogh one layer');
                    return;
                }
                this.saveDesign(this.urls.continue, this.continueCallback);
            }.bind(this));
        }
    },
    
    observeLayerControls: function() {
        document.observe('keydown', function(e) {
            if (e.ctrlKey == true && e.which  == 90) {
                this.history.undo();
            }

            if (e.ctrlKey == true && e.which  == 89) {
                this.history.redo();
            }

            if ((e.ctrlKey != false && e.altKey == false) && (e.which == 46 || e.which == 8)) {
                if ((this.canvas == null) || this.canvas == 'undefined') {
                    return;
                }
                var obj = this.canvas.getActiveObject();
                if (obj) {
                    this.layersManager.removeById(obj.get('uid'));
                }
            }
        }.bind(this));
        
        $(this.config.controls.undo).observe('click', function(e){
            e.stop();
            this.history.undo();
        }.bind(this));

        $(this.config.controls.redo).observe('click', function(e){
            e.stop();
            this.history.redo();
        }.bind(this));

        $(this.config.controls.flip_x).observe('click', function(e){
            e.stop();
            this.flipXLayer();
        }.bind(this));

        $(this.config.controls.flip_y).observe('click', function(e){
            e.stop();
            this.flipYLayer();
        }.bind(this));

        $(this.config.controls.zoom).observe('click', function(e){
            e.stop();
            this.zoom();
        }.bind(this))

        $(this.config.controls.allign_by_center).observe('click', function(e){
            e.stop();
            this.alignByCenterLayer();
        }.bind(this))
    },

    observeCanvasObjectModified: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        // Canvas events
        this.canvas.observe('object:modified', function(e) {
            var orig = e.target.originalState;
            // CASE 1: object has been moved
            setTimeout(function(){
                if (orig.left != e.target.left || orig.top != e.target.top) {
                    if ((e.target.left + e.target.width/2 <= 0) || (e.target.left - e.target.width/2 >= this.canvas.getWidth())) {
                        return;
                    } else if ((e.target.top + e.target.height/2 <= 0) || (e.target.top - e.target.height/2 >= this.canvas.getHeight())) {
                        return;
                    }
                    var cmd = new MovingCommand(
                        this.canvas,
                        this.canvas.getActiveObject(),
                        {left : orig.left, top: orig.top},
                        {left : e.target.left, top: e.target.top}
                    );
                    this.history.push(cmd);
                }
            }.bind(this), 1000);

            // CASE 2: object has been rotated
            if (orig.angle != e.target.angle) {
                var obj = this.canvas.getActiveObject();
                var cmd = new RotateCommand(this.canvas, obj, {angle: orig.angle}, {angle: e.target.angle});
                this.history.push(cmd);
            }

            // CASE 3: object has been resized
            if (orig.scaleX != e.target.scaleX || orig.scaleY != e.target.scaleY) {
                var cmd = new ResizeCommand(
                    this.canvas,
                    this.canvas.getActiveObject(),
                    {scaleX : orig.scaleX, scaleY: orig.scaleY},
                    {scaleX : e.target.scaleX, top: e.target.scaleY}
                );
                this.history.push(cmd);
            }
        }.bind(this));
    },

    observeCanvasObjectMoving: function() {
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }

        this.canvas.observe('object:moving', function(e){
            var obj = this.canvas.getActiveObject();
            if (!obj) {
                return;
            }
            setTimeout(function(){
                if ((obj.left + obj.width/2 <= 0) || (obj.left - obj.width/2 >= this.canvas.getWidth())) {
                    this.layersManager.removeById(obj.get('uid'));
                }

                if ((obj.top + obj.height/2 <= 0) || (obj.top - obj.height/2 >= this.canvas.getHeight())) {
                    this.layersManager.removeById(obj.get('uid'));
                }
            }.bind(this), 1000);
        }.bind(this));
    },

    observeCanvasObjectSelected: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        this.canvas.observe('object:selected', function(e) {
            this.layersManager.fireSelectEvent(this.canvas.getActiveObject());
        }.bind(this));
    },

    observeCanvasObjectRendered: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        this.canvas.observe('after:render', function(e) {
            var n = 0;
            this.canvas.forEachObject(function(o) {
                var l = o.left;
                var t = o.top;
                var w = Math.round(o.getWidth() / 2);
                var h = Math.round(o.getHeight() / 2);
                var f = false;
                if (l < w) f = true;
                if (t < h) f = true;
                if (l > this.canvas.getWidth() - w) f = true;
                if (t > this.canvas.getHeight() - h) f = true;
                if (f) {
                    n++;
                    this.layersManager.markAsOutside(o.get('uid'));
                } else {
                    this.layersManager.removeOutsideMark(o.get('uid'));
                }
            }.bind(this));

            if (n > 0) {
                this.designArea.addClassName('outside-warning');
            } else {
                this.designArea.removeClassName('outside-warning');
            }

            if (!this.canvas.getActiveObject()) {
                this.layersManager.fireBlurEvent();
            }
        }.bind(this));
    },

    observeProductImageChange: function(){
        Event.on($(this.opt.product_side_id), 'click', '.product-image', function(e, elem){
            this.changeProductImage(elem.readAttribute('data-id'));
        }.bind(this));
    },

    observeProductImageColorChange: function(){
        Event.on($('product-colors'), 'click', '.color-btn', function(e, elem){
            e.stop();
            var color = elem.getAttribute('color_id');
            if (this.currentColor != color) {
                if(!elem.hasClassName('selected')) {
                    elem.siblings().invoke('removeClassName', 'selected');
                }
                elem.addClassName('selected');
                this.changeProductColor(color);
            }
        }.bind(this));
    },

    flipXLayer: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        var activeObject = this.canvas.getActiveObject();
        if (!activeObject) {
            return;
        }
        var flip = false;
        var originalFlipX = activeObject.flipX;
        var originalFlipY = activeObject.flipY;
        if (activeObject.flipX == false) {
            flip = true;
        } else {
            flip = false;
        }

        var cmd = new FlipCommand(
            this.canvas,
            activeObject,
            {flipX : originalFlipX, flipY: originalFlipY},
            {flipX : flip, flipY: originalFlipY}
        );

        cmd.exec();
        this.history.push(cmd);
    },

    flipYLayer: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        var activeObject = this.canvas.getActiveObject();
        if (!activeObject) {
            return;
        }
        var flip = false;
        var originalFlipY = activeObject.flipY;
        var originalFlipX = activeObject.flipX;
        if (activeObject.flipY == false) {
            flip = true;
        } else {
            flip = false;
        }

        var cmd = new FlipCommand(
            this.canvas,
            activeObject,
            {flipY : originalFlipY, flipX: originalFlipX},
            {flipY : flip, flipX: originalFlipX}
        );

        cmd.exec();
        this.history.push(cmd);
    },

    alignByCenterLayer: function(){
        if ((this.canvas == null) || this.canvas == 'undefined') {
            return;
        }
        var activeObject = this.canvas.getActiveObject();
        if (!activeObject) {
            return;
        }
        var cmd = new AlignToCenterCommand(this.canvas, activeObject);
        cmd.exec();
        this.history.push(cmd);
    },

    // Zoom In
    zoom: function()
    {
        this.createZoomWindow();
        this.zoomWindow.showCenter(true);
    },

    createZoomCanvas: function() {
        if (this.config.product.images[this.currentColor][this.currentProd].orig_image == undefined) {
            return;
        }
        var origImage = this.config.product.images[this.currentColor][this.currentProd].orig_image;
        var imgUrl = origImage.url;
        var canvas = $('product-zoom-canvas');
        canvas.innerHTML = '';
        $('product-zoom-container').innerHTML = '';
        canvas.setAttribute('width', parseInt(this.config.imageMinSize.width));
        canvas.setAttribute('height', parseInt(this.config.imageMinSize.height));
        $('product-zoom-container').appendChild(canvas);
        var zoomCanvas = new fabric.Canvas(canvas);

        zoomCanvas.selection = false;
        fabric.Image.fromURL(imgUrl, function(obj) {
            var backgroundImage = this.prepareBackgroundZoomImage(obj);
            if (this.containerCanvases[this.currentProd].getObjects().length > 0) {
                var canvas = this.containerCanvases[this.currentProd];
                canvas.deactivateAll();
                canvas.renderAll();
                var image = canvas.toDataURL();
                var contextTop = canvas.contextTop;
                if (contextTop && contextTop != undefined) {
                    canvas.clearContext(contextTop);
                }
                fabric.Image.fromURL(image, function(obj){
                    var designImage = this.prepareDesignImageForZoom(obj)
                    var group = new fabric.Group([designImage, backgroundImage], {
                        left: parseInt(this.config.imageMinSize.width) / 2,
                        top: parseInt(this.config.imageMinSize.height) / 2,
                        hasControls: false,
                        hasBorders: false
                    });
                    group.padding = this.config.imageMinSize.width > this.config.imageMinSize.height
                        ? this.config.imageMinSize.width : this.config.imageMinSize.height;
                    zoomCanvas.add(group);
                    zoomCanvas.setActiveObject(group);
                    zoomCanvas.renderAll();
                }.bind(this))
            } else {
                var group = new fabric.Group([backgroundImage], {
                    left: parseInt(this.config.imageMinSize.width) / 2,
                    top: parseInt(this.config.imageMinSize.width) / 2,
                    hasControls: false,
                    hasBorders: false
                });
                group.padding = this.config.imageMinSize.width > this.config.imageMinSize.height
                    ? this.config.imageMinSize.width : this.config.imageMinSize.height;
                zoomCanvas.add(group);
                zoomCanvas.setActiveObject(group);
                zoomCanvas.renderAll();
            }
        }.bind(this));
    },

    prepareBackgroundZoomImage: function(obj) {
        var origImage = this.config.product.images[this.currentColor][this.currentProd].orig_image;
        var width = origImage.dimensions[0];
        var height = origImage.dimensions[1];
        if (height == parseInt(this.config.imageMinSize.height) && width == parseInt(this.config.imageMinSize.width)) {
            obj.lockMovementX = true;
            obj.lockMovementY = true;
        }

        obj.set({
            height: height,
            width: width,
            left: width / 2,
            top: height / 2,
            hasControls: false,
            hasBorders: true
        });

        return obj;
    },

    prepareDesignImageForZoom: function(obj){
        var currentImg = this.config.product.images[this.currentColor][this.currentProd];
        var origImage = this.config.product.images[this.currentColor][this.currentProd].orig_image;
        var frameWidth = dstWidth = currentImg.d[0];
        var frameHeight = dstHeight = currentImg.d[1];

        if ((origImage['dimensions'][0] / origImage['dimensions'][1]) >= frameWidth / frameHeight) {
            var dstHeight = Math.round((frameWidth / origImage['dimensions'][0]) * origImage['dimensions'][1]);
            var scale = origImage['dimensions'][0] / frameWidth;
        } else {
            var dstWidth = Math.round((frameHeight / origImage['dimensions'][1]) * origImage['dimensions'][0])
            var scale = origImage['dimensions'][1] / frameHeight;
        }

        var widthScale = origImage['dimensions'][0] / dstWidth;
        var heightScale = origImage['dimensions'][1] / dstHeight;


        obj.set({
            width: Math.round(currentImg.w * widthScale),
            height: Math.round(currentImg.h * heightScale),
            top: Math.round((currentImg.t * scale) - (frameHeight - dstHeight)),
            left: Math.round((currentImg.l * scale) - (frameWidth - dstWidth)),
            hasControls: false,
            hasBorders: false
        });

        return obj;
    },

    createZoomWindow: function()
    {
        if (!this.zoomWindow) {
            this.zoomWindow = new Window({
                className: 'magento',
                title: 'Zoom In',
                width: parseInt(this.config.imageMinSize.width),
                minWidth: parseInt(this.config.imageMinSize.width),
                height: parseInt(this.config.imageMinSize.height),
                minHeight: parseInt(this.config.imageMinSize.height),
                maximizable:false,
                minimizable:false,
                resizable:false,
                draggable:false,
                recenterAuto: false,
                showEffect:Effect.BlindDown,
                hideEffect:Effect.BlindUp,
                showEffectOptions: {duration: 0.4},
                hideEffectOptions: {duration: 0.4},
                onShow: this.createZoomCanvas.bind(this)
            });
            this.zoomWindow.setContent('product-zoom-container', true, true);
            this.zoomWindow.setZIndex(2000);
        }
    },

    initPrices: function() {
        this.pricesContainers = {};
        this.pricesContainers[0] = $('fixed_price');
        this.pricesContainers[1] = $('design_areas_price');
        this.pricesContainers[2] = $('images_price');
        this.pricesContainers[3] = $('texts_price');
    },

    reloadPrice: function(){
        if (!this.config.isProductSelected) {
            return;
        }

        var imagesPrice = 0;
        var textsPrice = 0;
        var designAreasPrice = 0;
        var subTotal = 0;
        this.designPrices = {};

        if (this.prices.fixed_price > 0) {
            var fixedPrice = parseInt(this.prices.fixed_price);
            var fixedPriceConfig = {
                price: fixedPrice,
                type: "fixed"
            };
            subTotal+=fixedPrice;
            if (optionsPrice != undefined) {
                optionsPrice.addCustomPrices('design_fixed_price', fixedPriceConfig);
            }
            this.designPrices['fixed_price'] = fixedPrice;
        }

        for (var id in this.containerCanvases) {
            var canvas = this.containerCanvases[id];
            var designAreaPrice = 0;
            if (canvas && canvas != 'undefined') {
                var canvasCount = canvas.getObjects().length;
                if (canvasCount > 0) {
                    if (this.config.product.images[this.currentColor].hasOwnProperty(id)) {
                        var designArea = this.config.product.images[this.currentColor][id];
                        designAreaPrice = parseInt(designArea.ip);
                        if (designAreaPrice > 0) {
                            designAreasPrice += designAreaPrice;
                            subTotal += designAreaPrice;
                        }
                        var imagesCount = 0;
                        var textsCount = 0;
                        var imagePrice = parseInt(this.prices.image_text);
                        var textPrice = parseInt(this.prices.text_price);
                        canvas.getObjects().each(function(object){
                            if (object.type == 'custom_text') {
                                textsCount++;
                                if (textPrice > 0) {
                                    textsPrice += textPrice;
                                    subTotal += textPrice;
                                }
                            } else if (object.type == 'image'){
                                imagesCount++;
                                if (imagePrice > 0) {
                                    imagesPrice += imagePrice;
                                    subTotal += imagePrice;
                                }
                            }
                        }.bind(this));
                        if (this.designPrices['images'] == undefined) {
                            this.designPrices['images'] = {};
                        }
                        this.designPrices['images'][id] = designAreaPrice
                            + (imagePrice * imagesCount) + (textPrice * textsCount);

                        if (this.designPrices['texts_count'] == undefined) {
                            this.designPrices['texts_count'] = textsCount;
                        } else {
                            this.designPrices['texts_count'] += textsCount;
                        }
                        if (this.designPrices['images_count'] == undefined) {
                            this.designPrices['images_count'] = imagesCount;
                        } else {
                            this.designPrices['images_count'] += imagesCount;
                        }
                    }
                }
            }
        }
        this.designPrices['sub_total'] = subTotal;
        this.designPrices['design_areas_price'] = designAreasPrice;
        this.designPrices['texts_price'] = textsPrice;
        this.designPrices['images_price'] = imagesPrice;

        if (optionsPrice != undefined) {
            optionsPrice.addCustomPrices('design_area_price', {price: designAreasPrice, type: 'fixed'});
            optionsPrice.addCustomPrices('design_text_price', {price: textsPrice, type: 'fixed'});
            optionsPrice.addCustomPrices('design_image_price', {price: imagesPrice, type: 'fixed'});
            optionsPrice.setDuplicateIdSuffix('-design');
            optionsPrice.reload();
        }

        this.updatePriceMoreInfo(this.designPrices);
    },

    observePriceMoreInfo: function() {
        Event.on($('design_price_container'), 'click', '#price-more-info-switcher', function(e, elm){
            var moreInfoContainer = $('price-more-info');
            e.stop();
            if (moreInfoContainer) {
                moreInfoContainer.toggle();
            }
        });
    },

    updatePriceMoreInfo: function(prices) {
        $H(this.pricesContainers).each(function(pair) {
            if (prices.hasOwnProperty(pair.value.id)) {
                var price = prices[pair.value.id];
                if (price > 0 && $(pair.value).select('.price')[0]) {
                    var formattedPrice = optionsPrice.formatPrice(price);
                    $(pair.value).select('.price')[0].innerHTML = formattedPrice;
                    $(pair.value).show();
                } else if (price == 0) {
                    $(pair.value).hide();
                }
            }
        });
    }
};

GoMage.ProductNavigation = function(filterUrl, productUrl){
    this.productDesigner = window.w;
    this.opt = {
        filterUrl: filterUrl,
        productUrl: productUrl,
        navigationFiltersId: 'navigation-filters',
        navigationProducts: 'navigation_listing'
    };

    this.initProductView();
    this.observePager();
    this.observeFiltersChange();
    this.observeProductSelect();
};

GoMage.ProductNavigation.prototype = {
    initProductView: function() {
        Event.on($(this.opt.navigationProducts), 'mouseover', '.item', function(e, elm){
            e.stop();
            var containerWidth = $(this.opt.navigationProducts).getWidth();
            elm.down('.display-product').setStyle({
                display : 'block',
                right : containerWidth + 'px'
            });
        }.bind(this));

        Event.on($(this.opt.navigationProducts), 'mouseout', '.item', function(e, elm){
            e.stop();
            var containerWidth = $(this.opt.navigationProducts).getWidth();
            elm.down('.display-product').setStyle({
                display : 'none',
                right : containerWidth + 'px'
            });
        }.bind(this));
    },

    observePager: function(){
        var pagerLinks = $$('#' + this.opt.navigationProducts + ' .pager a');
        pagerLinks.invoke('observe', 'click', function(event) {
            event.stop();
            var pageHrefSplit = this.href.match('[&?]+p=([0-9]+)');
            var page;
            if(typeof pageHrefSplit[1] === 'undefined') {
                page = 0;
            } else {
                page = parseInt(pageHrefSplit[1]);
            }

            if(!isNaN(page)) {
                var data = [];
                data['p'] = page;
                this.prepareAndSubmitData(this.opt.filterUrl, this.updateDataOnFilterApply, data);
            }
        }.bind(this));
    },

    observeFiltersChange: function() {
        Event.on($(this.opt.navigationFiltersId), 'change', '.filter_selector', function(e, elm){
            e.stop();
            var data = {};
            $$('.filter_selector').each(function(element) {
                data[element.name] = element.value;
            }.bind(this));

            this.prepareAndSubmitData(this.opt.filterUrl, this.updateDataOnFilterApply.bind(this), data);
        }.bind(this));
    },

    observeProductSelect: function() {
        Event.on($(this.opt.navigationProducts), 'click', '.product-image', function(e, elem){
            e.stop();
            var productId = elem.getAttribute('product_id');
            if (productId && productId != undefined) {
                var data = { id: productId };
            }
            this.prepareAndSubmitData(this.opt.productUrl, this.updateDataOnProductChoose.bind(this), data);
        }.bind(this));
    },

    prepareAndSubmitData: function(url, callbackFunc, data){
        if(typeof data === 'undefined') {
            var data = {};
        }
        data['ajax'] = true;

        new Ajax.Request(url, {
            method:'post',
            parameters: data,
            onSuccess: function(transport) {
                var response = transport.responseText.evalJSON();
                if(response.status == 'success') {
                    callbackFunc(response);
                    if(typeof data['id'] !== 'undefined') {
                        window.history.pushState({}, '', '//' + location.host + location.pathname + '?id='+data['id']);
                    }
                } else if(response.status == 'error') {
                    // TODO
                    alert('Something went wrong...');
                }
            }.bind(this),
            onFailure: function() {
                // TODO
                alert('Something went wrong...');
            }
        });
    },

    updateDataOnFilterApply: function(response) {
        if (response.navigation_filters) {
            $(this.opt.navigationFiltersId).update(response.navigation_filters);
        }

        if (response.navigation_prodcuts) {
            $(this.opt.navigationProducts).update(response.navigation_prodcuts);
        }
    },

    updateDataOnProductChoose: function(response) {
        if (response.product_settings) {
            this.productDesigner.changeProduct(response);
        }
    }
};

GoMage.Designer = function(filterUrl){
    this.opt = {
        filterUrl: filterUrl
    };
    this.productDesigner = window.w;
    this.observeFilterFields();
    this.observeImageSelect();
};

GoMage.Designer.prototype = {
    observeImageSelect: function() {
        Event.on($('cliparts-list'), 'click', '.clipart-image', function(e, elm){
            e.stop();
            var img = e.target || e.srcElement;
            fabric.Image.fromURL(img.src, function(obj) {
                obj.set({
                    width: 64,  // move to config
                    height: 64 // move to config
                });

                var cmd = new InsertCommand(this.productDesigner, obj, true);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }.bind(this));
        }.bind(this));
    },

    filterImages: function() {
        var data = {};
        data['ajax'] = true;
        data['mainCategory'] = $('mainCategoriesSearchField').value;
        data['subCategory'] = $('subCategoriesSearchField').value;
        data['tags'] = $('tagsSearchField').value;

        new Ajax.Request(this.opt.filterUrl, {
            method:'post',
            parameters: data,
            onSuccess: function(transport) {
                var response = transport.responseText.evalJSON();
                if(response.status == 'success') {
                    if (response.hasOwnProperty('filters')) {
                        $('cliparts-filters').update(response.filters);
                    }
                    if (response.hasOwnProperty('cliparts')) {
                        $('cliparts-list').update(response.cliparts);
                    }
                } else {
                    //TODO
                    alert('Something went wrong...');
                }
            },
            onFailure: function() {
                // TODO
                alert('Something went wrong...');
            }
        });
    },

    observeFilterFields: function(){
        Event.on($('cliparts-search-btn'), 'click', '#cliparts-search-btn', function(e, elm){
            e.stop();
            this.filterImages();
        }.bind(this));
        Event.on($('cliparts-filters'), 'change', '#mainCategoriesSearchField, #subCategoriesSearchField', function(e, elm){
            e.stop();
            this.filterImages();
        }.bind(this));
    },
};

GoMage.TextEditor = function(defaultFontFamily, defaultFontSize) {
    this.productDesigner = window.w;
    this.colorPicker = null;
    this.curvedTextOpt = {
        radiusX : 0,
        radiusY : 100,
        startAngle : 1,
        endAngle : 2.1
    };
    this.defaultTextOpt = {
        fontFamily: defaultFontFamily,
        fontsize: defaultFontSize
    };
    this.fontSelector = $('font-selector');
    this.addTextTextarea = $('add_text_textarea');
    this.addTextButton = $('add_text_button');
    this.addTextBtnBold = $('add_text_btn_bold');
    this.addTextBtnItalic = $('add_text_btn_italic');
    this.addTextBtnUnderline = $('add_text_btn_underline');
    this.addTextBtnVertOut = $('add_text_btn_vert_out');
    this.fontSizeSelector = $('font_size_selector');
    this.curvedTextButton = $('curved-text-button');
    this.btnShadowText = $('shadow-button');
    this.btnOutlineText = $('outline-button');
    this.addTextColorsPanel = $('add_text_colors_panel');
    this.outlineStrokeWidthRange = $('outline_range');

    this.observeTextTabShow();
    this.initColorPickers();
    this.observeTextColorChange();
    this.observeFontChange();
    this.observeAddText();
    this.observeFontSizeChange();
    this.observeFontStyleControls();
    this.observeCurvedTextButton();
    this.observeCurvedTextControls();
    this.observeShadowButton();
    this.observeShadowControls();
    this.observeOutlineButton();
    this.observeOutlineControls();
};

GoMage.TextEditor.prototype = {
    observeTextTabShow: function() {
        document.observe('addTextTabShow', function(e){
            var textObj = e.obj
            if (textObj) {
                this.addTextTextarea.value = textObj.text;
                if (textObj.fontFamily) {
                    this.fontSelector.value = textObj.fontFamily;
                }
                if (textObj.fontSize) {
                    this.fontSizeSelector.value = textObj.fontSize;
                }
                if (textObj.strokeWidth) {
                    this.outlineStrokeWidthRange.value = textObj.strokeWidth;
                }
                if (textObj.textShadowParams.x) {
                    $('shadow_x_range').value = textObj.textShadowParams.x;
                }
                if (textObj.textShadowParams.y) {
                    $('shadow_y_range').value = textObj.textShadowParams.y;
                }
                if (textObj.textShadowParams.blur) {
                    $('shadow_blur').value = textObj.textShadowParams.blur;
                }
            } else {
                this.addTextTextarea.value = '';
                this.fontSelector.value = this.defaultTextOpt.fontFamily;
                this.fontSizeSelector.value = this.defaultTextOpt.fontsize;
                this.outlineStrokeWidthRange.value = this.outlineStrokeWidthRange.getAttribute('min');
                $('shadow_x_range').value = 0;
                $('shadow_y_range').value = 0;
                $('shadow_blur').value = 0;
            }

        }.bind(this));
    },

    initColorPickers: function() {
        var colorPickers = $$('.color-picker');
        colorPickers.each(function(element) {
            this.colorPicker = new ColorPicker(element, 20, 230);
            this.colorPicker.getNode().observe('select', this.selectColorOnPicker.bind(this));
        }.bind(this));
    },

    selectColorOnPicker: function(e) {
        var elem = e.target || e.srcElement;
        var obj = this.productDesigner.canvas.getActiveObject();
        if (obj && obj.type == 'custom_text') {
            if(elem.id) {
                var data;
                switch(elem.id) {
                    case 'color': {
                        data = e.hex;
                        break;
                    }
                    case 'textShadow': {
                        data = {color : e.hex};
                        break;
                    }
                    default: {
                        data = e.hex;
                    }
                }
                obj['set' + $ucfirst(elem.id)](data);
                this.productDesigner.canvas.renderAll();
            }
        }
    },

    /**
     * Set Text Color function
     */
    setTextColor: function(e) {
        var elem = e.target || e.srcElement;
        if(!elem.hasClassName('selected')) {
            elem.siblings().invoke('removeClassName', 'selected');
        }
        elem.addClassName('selected');
        var color = elem.className.match(/color-code-([0-9A-Z]{6})/)[1];
        var color = '#' + color;
        var obj = this.productDesigner.canvas.getActiveObject();
        if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
            var cmd = new TransformCommand(this.productDesigner.canvas, obj, {color: color});
            cmd.exec();
            this.productDesigner.history.push(cmd);
        }
    },

    observeTextColorChange: function(){
        this.addTextColorsPanel.childElements().invoke('observe', 'click', this.setTextColor.bind(this));
    },

    observeFontChange: function(){
        this.fontSelector.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && obj.type == 'custom_text') {
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, {fontFamily: elem.value});
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));
    },

    observeAddText: function(){
        this.addTextButton.observe('click', function() {
            if (!this.addTextTextarea.value){
                return;
            }
            var textObjectData = {
                fontsize : this.fontSizeSelector.value,
                fontFamily : this.fontSelector.value
            };
            var textObject = new fabric.CustomText(this.addTextTextarea.value, this.defaultTextOpt);
            textObject.setFontsize(this.fontSizeSelector.value);
            textObject.setData(textObjectData);

            var cmd = new InsertCommand(this.productDesigner, textObject, true);
            cmd.exec();
            this.productDesigner.history.push(cmd);
        }.bind(this));

        this.addTextTextarea.observe('keyup', function(e) {
            if (this.timeout != 'undefined' || this.timeout != null) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(function(){
                var elem = e.target || e.srcElement;
                if (!elem.value) {
                    this.productDesigner.layersManager.removeById(obj.get('uid'));
                    return;
                }
                var currentValue = elem.value;
                var obj = this.productDesigner.canvas.getActiveObject();
                if(obj  && (obj.type == 'text' || obj.type == 'custom_text')) {
                    if(currentValue != obj.getText()) {
                        var params = {
                            fontsize: this.fontSizeSelector.value,
                            text: currentValue,
                            left: obj.left,
                            top: obj.top
                        };
                        var cmd = new TransformCommand(this.productDesigner.canvas, obj, params);
                        cmd.exec();
                        this.productDesigner.history.push(cmd);
                    }
                }
            }.bind(this), 2500);
        }.bind(this));
    },

    observeFontSizeChange: function(){
        this.fontSizeSelector.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, {fontsize: parseInt(elem.value)});
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));
    },

    observeFontStyleControls: function(){
        this.addTextBtnBold.observe('click', function(e) {
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
                var params = {fontWeight: (obj.fontWeight == '400' ? 'bold' : '400')}
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, params);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));

        this.addTextBtnItalic.observe('click', function(e) {
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
                var params = {fontStyle: (obj.fontStyle == '' ? 'italic' : '')}
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, params);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));

        this.addTextBtnUnderline.observe('click', function(e) {
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
                var params = {textDecoration: (obj.textDecoration == '' ? 'underline' : '')}
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, params);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));

        this.addTextBtnVertOut.observe('click', function(e) {
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && (obj.type == 'text' || obj.type == 'custom_text')) {
                var params = {verticalOutput: (obj.verticalOutput == false ? true : false)}
                var cmd = new TransformCommand(this.productDesigner.canvas, obj, params);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));
    },

    openConfigContainer: function(elem) {
        elem.siblings().invoke('removeClassName', 'active');
        elem.addClassName('active');
        var configClass = elem.id.replace('-button', '-config');
        var configElement = $$('.' + configClass)[0];
        configElement.siblings().invoke('setStyle', {display:'none'});
        if(configElement.getStyle('display') == 'none') {
            configElement.setStyle({display:'block'});
        } else {
            configElement.setStyle({display:'none'});
        }
    },

    observeCurvedTextButton: function(){
        this.curvedTextButton.observe('click', function(e){
            var elem = e.target || e.srcElement;
            this.openConfigContainer(elem);
        }.bind(this));
    },

    observeCurvedTextControls: function(){
        var curvedTextParams = Object.clone(this.curvedTextOpt);
        var rxRangeInput = $('rx_range');

        rxRangeInput.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            var obj = this.productDesigner.canvas.getActiveObject();
            if (obj && obj.type == 'custom_text') {
                if (obj.verticalOutput) {
                    curvedTextParams.radiusX = elem.max;
                    curvedTextParams.radiusY = elem.value;
                    curvedTextParams.startAngle = 1/2;
                    curvedTextParams.endAngle = 3/2 + 0.1;
                } else {
                    curvedTextParams.radiusX = elem.value;
                    curvedTextParams.radiusY = elem.max;
                }

                var origin = obj.originalState;
                var cmd = new CurveTextCommand(this.productDesigner.canvas, obj, origin, curvedTextParams);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }
        }.bind(this));
    },

    observeShadowButton: function(){
        this.btnShadowText.observe('click', function(e){
            var elem = e.target || e.srcElement;
            this.openConfigContainer(elem);
        }.bind(this));
    },

    observeShadowControls: function(){
        var shadowOffsetY =  $('shadow_y_range');
        var shadowOffsetX =  $('shadow_x_range');
        var shadowBlur =  $('shadow_blur');

        shadowOffsetY.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            this.setShadow({textShadow: {y : elem.value}});
        }.bind(this));

        shadowOffsetX.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            this.setShadow({textShadow: {x : elem.value}});
        }.bind(this));

        shadowBlur.observe('change', function(e) {
            var elem = e.target || e.srcElement;
            this.setShadow({textShadow: {blur : elem.value}});
        }.bind(this));
    },

    setShadow: function(shadowParams) {
        var obj = Object.clone(this.productDesigner.canvas.getActiveObject());
        if (obj && obj.type == 'custom_text') {
            var cmd = new TransformCommand(this.productDesigner.canvas, obj, shadowParams);
            cmd.exec();
            //TODO Fix history
            this.productDesigner.history.push(cmd);
        }
    },

    observeOutlineButton: function(){
        this.btnOutlineText.observe('click', function(e){
            var elem = e.target || e.srcElement;
            if (elem.hasClassName('active')){
                this.outlineStrokeWidthRange.value = 0;
                this.setOutline({strokeWidth: 0, strokeStyle: ''});
            }
            this.openConfigContainer(elem);
        }.bind(this));
    },

    observeOutlineControls: function(){
        this.outlineStrokeWidthRange.observe('change', function(e) {
            if (this.timeout != 'undefined' || this.timeout != null) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(function(){
                var elem = e.target || e.srcElement;
                this.setOutline({strokeWidth: elem.value});
            }.bind(this), 500);

        }.bind(this));
    },

    setOutline: function(strokeData){
        var obj = this.productDesigner.canvas.getActiveObject();
        if (!obj || obj.type != 'custom_text') {
            return;
        }
        this.outlineStrokeWidthRange.value = strokeData.strokeWidth;
        var cmd = new TransformCommand(this.productDesigner.canvas, obj, strokeData);
        cmd.exec();
        this.productDesigner.history.push(cmd);
    }
};

GoMage.ImageUploader = function(){
    this.observeLicenseAgreements();
    this.observeLicenseAgreementsMoreInfo();
    this.observeImageConditions();
    window.onload = this.observeSubmitForm.bind(this);
    this.productDesigner = window.w;
    this.observeImageSelect();
};

GoMage.ImageUploader.prototype = {
    observeLicenseAgreements: function(){
        if (!$('licence_agreements')){
            return;
        }
        $('licence_agreements').observe('click', function(){
            var inputWrapper = $('file-input-box');
            if(inputWrapper.getStyle('display') == 'none') {
                inputWrapper.setStyle({display:'block'});
            } else {
                inputWrapper.setStyle({display:'none'});
            }
        });
    },

    observeLicenseAgreementsMoreInfo: function(){
        if (!$('license-agreements-link')) {
            return;
        }

        $('license-agreements-link').observe('mouseover', function(){
            $('license-agreemants').show();
        }.bind(this));

        $('license-agreements-link').observe('mouseout', function(){
            $('license-agreemants').hide();
        }.bind(this));
    },

    observeImageConditions: function() {
        if (!$('image-conditions')) {
            return;
        }

        $('filesToUpload').observe('mouseover', function(){
            $('image-conditions').show();
        });

        $('filesToUpload').observe('mouseout', function(){
            $('image-conditions').hide();
        });
    },

    /**
     * TODO Add upload for IE
     */
    observeSubmitForm: function(){
        $('uploadImages').onsubmit = function(){
            $('uploadImages').target = 'iframeSave';
            $('iframeSave').onload = function() {
                var response = window.frames['iframeSave'].document.body.innerHTML;
                $('uploadedImages').update(response);
                $('filesToUpload').value = '';
            }.bind(this);
        }.bind(this);
    },

    observeImageSelect: function(){
        Event.on($('uploadedImages'), 'click', '.clipart-image', function(e, elm){
            e.stop();
            var img = e.target || e.srcElement;
            fabric.Image.fromURL(img.src, function(obj) {
                obj.set({
                    width: 64,  // move to config
                    height: 64 // move to config
                });

                var cmd = new InsertCommand(this.productDesigner, obj, true);
                cmd.exec();
                this.productDesigner.history.push(cmd);
            }.bind(this));
        }.bind(this));
    }
};


// COLOR PICKER
var ColorPicker = function(canvasObj, height, width) {
    var self   = this;
    var canvas = canvasObj;

    canvas.height = height;
    canvas.width  = width;

    var colorctx = canvas.getContext('2d');
    var gradient = colorctx.createLinearGradient(0, 0, canvas.width, 0);

    gradient.addColorStop(0,    'rgb(255,   0,   0)');
    gradient.addColorStop(0.15, 'rgb(255,   0, 255)');
    gradient.addColorStop(0.33, 'rgb(0,     0, 255)');
    gradient.addColorStop(0.49, 'rgb(0,   255, 255)');
    gradient.addColorStop(0.67, 'rgb(0,   255,   0)');
    gradient.addColorStop(0.84, 'rgb(255, 255,   0)');
    gradient.addColorStop(1,    'rgb(255,   0,   0)');

    colorctx.fillStyle = gradient;
    colorctx.fillRect(0, 0, canvas.width, canvas.height);

    gradient = colorctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0,   'rgba(255, 255, 255, 1)');
    gradient.addColorStop(0.5, 'rgba(255, 255, 255, 0)');
    gradient.addColorStop(0.5, 'rgba(0,     0,   0, 0)');
    gradient.addColorStop(1,   'rgba(0,     0,   0, 1)');

    colorctx.fillStyle = gradient;
    colorctx.fillRect(0, 0, canvas.width, canvas.height);

    this.ctx = colorctx;
    this.palete = canvas;

    canvas.observe('click', function(e) {
        var c = canvas.cumulativeOffset();
        var x = e.pageX - c.left;
        var y = e.pageY - c.top;

        var pixel = colorctx.getImageData(x, y, 1, 1);
        var color = 'rgb(' + pixel.data[0] + ', ' + pixel.data[1] + ', ' + pixel.data[2] + ')';

        var event = document.createEvent('Event');

        event.rgb = color;
        event.hex = self.rgbToHex(pixel.data[0], pixel.data[1], pixel.data[2]);

        event.initEvent('select', true, true);
        canvas.dispatchEvent(event);
    });
};

/** @see http://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb */
ColorPicker.prototype = {
    rgbToHex : function(r, g, b) {
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    },

    getNode : function() { return this.palete; }
};
// END OF COLOR PICKER

// ------------------------------------------------------
// Extending Fabric.js classes
// ------------------------------------------------------

/**
 * fabric.CustomText
 * @parent fabric.Group
 *
 * Added support for:
 * - curved text
 * - vertical output
 */
fabric.CustomText = fabric.util.createClass(fabric.Group, {
    type : 'custom_text',

    text : '',

    fontFamily : 'Arial',

    fontWeight : '400',

    fontStyle : '',

    color : 'rgd(255, 255, 255)',

    textDecoration : '',

    fontsize : 16,

    verticalOutput : false,

    textShadow : '#0000 0px 0px 0px',

    textShadowParams : {color : '#000', x : 0, y : 0, blur : 0},

    strokeStyle : '#000000',

    strokeWidth : 0.05,
    centerTransform: false,

    initialize : function(text, options) {
//        this.centerTransform = false;
        this.text = text;
        this.canvas = w.canvas;
        if(typeof options === 'undefined') {
            options = {
                type : this.type,
                fontFamily : this.fontFamily,
                fontWeight : this.fontWeight,
                fontStyle : this.fontStyle,
                color : this.color,
                textDecoration : this.textDecoration,
                fontsize : this.fontsize,
                verticalOutput : this.verticalOutput,
                textShadow : this.textShadow,
                textShadowParams : this.textShadowParams,
                strokeStyle : this.strokeStyle,
                strokeWidth : this.strokeWidth
            };
        }
        this.curved = {
            radiusX: 0,
            radiusY: 100,
            startAngle: 1,
            endAngle: 2.1
        }
        this.stateProperties.push('curved');
        this.callSuper('initialize', this._createGroupFromText(text), options);
        this.setData(options);
        options && this.set('name', options.name);
        options && this.set('id', options.id);
    },

    setData : function(name, value) {
        if(typeof name == 'string') {
            this[name] = value;
            if(this.getObjects()) {
                this.forEachObject(function(obj) { obj[name] = value; });
            }
        } else if(typeof name == 'array' || typeof name == 'object') {
            for(var key in name) {
                this.setData(key, name[key]);
            }
        }
    },

    getData : function() {
        var data = {};
        for(key in this) {
            if(typeof this[key] !== 'function') {
                data[key] = this[key];
            }
        }
        return data;
    },

    setFontFamily : function(value) {
        this.fontFamily = value;
        this.forEachObject(function(obj) { obj.fontFamily = value; });
    },

    setStrokeWidth : function(value) {
        this.strokeWidth = value;
        this.forEachObject(function(obj) { obj.strokeWidth = value; });
    },

    setStrokeStyle : function(value) {
        this.strokeStyle = value;
        this.forEachObject(function(obj) { obj.strokeStyle = value; });
    },

    setFontStyle : function(value) {
        this.fontStyle = value;
        this.forEachObject(function(obj) { obj.fontStyle = value; });
    },

    setFontWeight : function(value) {
        this.fontWeight = value;
        this.forEachObject(function(obj) { obj.fontWeight = value; });
    },

    setTextDecoration : function(value) {
        // TODO: big big troubles with it...
        this.textDecoration = value;
        this.forEachObject(function(obj) { obj.textDecoration = value; });
    },

    setTextShadow : function(conf) {
        var self = this;
        this.textShadowParams.color = conf.color || this.textShadowParams.color;
        this.textShadowParams.x     = conf.x || this.textShadowParams.x;
        this.textShadowParams.y     = conf.y || this.textShadowParams.y;
        this.textShadowParams.blur  = conf.blur || this.textShadowParams.blur;

        var p = this.textShadowParams;

        this.textShadow = p.color + ' ' + p.x + 'px ' + p.y + 'px ' + p.blur + 'px';
        this.forEachObject(function(obj) { obj.textShadow = self.textShadow; });
    },

    getText : function() {
        return this.text;
    },

    setText : function(text) {
        this.initialize(text);
        this.text = text;
    },

    setFontsize : function(value) {
        this.fontsize = parseInt(value);

        var i  = 0;
        var l  = this.objects.length;
        var gl = this.get('left');
        var gt = this.get('top');

        for (i; i < l; i++) {
            var obj = this.objects[i].setFontsize(this.fontsize);
            this.objects[i].top  = gt;
            this.objects[i].left = gl + (obj.getWidth() * i);
        }

        this._calcBounds();
        this._updateObjectsCoords();

        this.top  = gt;
        this.left = gl;

        this.setVerticalOutput(this.verticalOutput);
    },

    setColor : function(value) {
        this.color = value;
        this.forEachObject(function(obj) { obj.setColor(value); });
    },

    setCharsInterval : function(value) {

    },

    // Custom methods
    setVerticalOutput : function(flag) {
        this.verticalOutput = flag ? true : false;

        var i  = 0;
        var l  = this.objects.length;
        var gl = this.get('left');
        var gt = this.get('top');
        var p  = 0;
        var fs = parseInt(this.fontsize);

        if (this.verticalOutput) {
            p = this.objects[i].getHeight();
        } else {
            p = this.objects[i].getWidth();
        }

        for (i; i < l; i++) {
            if (this.verticalOutput) {
                this.objects[i].left = gl;
                this.objects[i].top  = gt + (p * i)
            } else {
                this.objects[i].left = gl + (fs/2) * i ;
                this.objects[i].top  = gt;
            }
        }

        this._calcBounds();
        this._updateObjectsCoords();

        this.top  = gt;
        this.left = gl;
    },

    setCurvedData: function(params){
        for (var name in params) {
            if (params.hasOwnProperty(name)) {
                this.curved[name] = params[name];
            }
        }
    },

    setArc : function(params) {
//        if (!this.originalState.curved) {
//            this.originalState.curved = {};
//        }
//
//        this.originalState.curved = Object.clone(this.curved);
        this.setCurvedData(params);
        var width = this.getWidth();
        var height = this.getHeight();
        if (this.verticalOutput) {
            var rX = ((height / 2) * this.curved.radiusX) / 100;
            var rY = ((height / 2) * this.curved.radiusY) / 100;
        } else {
            var rX = ((width / 2) * this.curved.radiusX) / 100;
            var rY = ((width / 2) * this.curved.radiusY) / 100;
        }
        var gl = this.left;
        var gt = this.top;

        var objects = this.getObjects();

        var count = objects.length;
        var i = this.curved.startAngle * Math.PI;
        var m = this.curved.endAngle * Math.PI;
        var s = (m - i) / count;
        for (var j = 0; j < count; j++) {
            var left = this.left + (rY * Math.cos(i));
            var top = this.top + (rX * Math.sin(i));
            objects[j].top  = top;
            objects[j].left = left;
            i+=s;
        }

        this._calcBounds();
        this._updateObjectsCoords();

        this.left = gl;
        this.top  = gt;


        if (this.verticalOutput) {
            this.setHeight(height);
        } else{
            this.setWidth(width);
        }
    },

    _createGroupFromText : function(text) {
        var t = text.split('');
        var g = [];
        var fs = parseInt(this.fontsize);

        for (var i = 0; i < t.length; i++) {
            var ch = new fabric.Text(t[i], {top : 100, left : (fs / 2) * i, strokeWidth : this.strokeWidth});
            g.push(ch);
        }

        return g;
    }
});


// ------------------------------------------------------
// Layers manager
// ------------------------------------------------------

var LayersManager = function(w) {
    var self = this;
    // link on Workspace
    this.w = w;
    // active layer ID
    this.active = null;
    // layers array
    this.layers = {};
    // layers which are outside of borders
    this.outside = {};

    document.observe('PdLayerSelect', function(e) {
        var obj = e.obj;
        if (self.active == obj.get('uid')) {
            return;
        }
        self.active = obj.get('uid');

        if (obj.type == 'image' && this.w.config.isDesignedEnabled) {
            var tabContentElement = $('pd_add_design-content');
        } else if (obj.type == 'custom_text' && this.w.config.isTextEnabled) {
            var tabContentElement = $('pd_add_text-content');
            var event = document.createEvent('Event');
            event.obj = obj;
            event.initEvent('addTextTabShow', true, true);
            document.dispatchEvent(event);
        }
        if(tabContentElement && !tabContentElement.visible()) {
            tabContentElement.siblings().invoke('hide');
            tabContentElement.setStyle({display: 'block'});
        }
    }.bind(this));

    document.observe('PdLayerBlur', function(e) {
        if (self.active == null) return;
        self.active = null;
    });
};

LayersManager.prototype = {
    add : function(obj) {
        this.active = obj.get('uid');
        this.layers[this.active] = obj;
    },

    remove : function(obj) {
        this.removeById(obj.get('uid'));
    },

    removeById : function(id) {
        if (!this.layers[id]) {
            return;
        }
        var cmd = new RemoveCommand(this.w, this.layers[id]);
        cmd.exec();
        this.w.history.push(cmd);
        this.layers[id] = null;
    },

    removeOnlyLayer : function(obj) {
        if (!obj) {
            return;
        }
        var id = obj.get('uid');
        this.layers[id] = null;
    },

    setActive : function(id) {
        if (this.active == id) return;
        this.w.canvas.setActiveObject(this.layers[id]);
        this.active = id
    },

    markAsOutside : function(id) {
        if (this.outside[id] == true) return;
        this.outside[id] = true;
    },

    removeOutsideMark : function(id) {
        if (!this.outside[id]) return;
        this.outside[id] = false;
    },

    up : function(id) {

    },

    down : function(id) {

    },

    fireSelectEvent : function(obj) {
        var event = document.createEvent('Event');
        event.obj = obj;
        event.initEvent('PdLayerSelect', true, true);
        document.dispatchEvent(event);
    },

    fireBlurEvent : function() {
        var event = document.createEvent('Event');
        event.initEvent('PdLayerBlur', true, true);
        document.dispatchEvent(event);
    },

    clear: function() {
        this.layers = {};
        this.active = null;
        this.outside = {};
    }
};

// ------------------------------------------------------
// History managment
// ------------------------------------------------------

var History = function() {
    this.undoStack = [];
    this.redoStack = [];
    this.limit     = 50;
    this.onChangeCallbacks = [];
};

History.prototype = {
    push : function(cmd) {
        this.undoStack.push(cmd);
        this.fireChangeEvent();
    },

    undo : function() {
        var cmd = this.undoStack.pop();
        if (!cmd) return;
        cmd.unexec();
        this.redoStack.push(cmd);
        this.fireChangeEvent();
    },

    redo : function() {
        var cmd = this.redoStack.pop();
        if (!cmd) return;
        cmd.exec();
        this.undoStack.push(cmd);
        this.fireChangeEvent();
    },

    clear : function() {
        // TODO
        this.undoStack = [];
        this.redoStack = [];
    },

    fireChangeEvent : function() {
        var event = document.createEvent('Event');
        event.history = this;
        event.initEvent('PdChangeHistory', true, true);
        document.dispatchEvent(event);
    }
};

/**
 * Insert object
 */
var InsertCommand = function(w, obj, alignByCenter) {
    var c = w.canvas;
    // set unique ID
    obj.set('uid', 'id_' + Date.now());
    return {
        exec : function() {
            // add object on canvas
            c.add(obj);
            // add object to layers manager
            w.layersManager.add(obj);
            // alignment
            if (alignByCenter) {
                obj.center()
            };
            c.setActiveObject(obj);
            obj.setCoords();
            c.renderAll();
            w.reloadPrice();
        },
        unexec : function() {
            w.layersManager.removeOnlyLayer(obj);
            c.remove(obj);
            w.reloadPrice();
        }
    };
};

/**
 * Remove object
 */
var RemoveCommand = function(w, obj) {
    var c = w.canvas;
    return {
        exec : function() {
            w.layersManager.removeOutsideMark(obj.get('uid'));
            w.layersManager.removeOnlyLayer(obj);
            c.remove(obj);
            w.reloadPrice();
        },
        unexec : function() {
            c.add(obj);
            c.setActiveObject(obj);
            if ((obj.left + obj.width/2 <= 0) || (obj.left - obj.width/2 >= c.getWidth())) {
                obj.center();
            } else if ((obj.top + obj.height/2 <= 0) || (obj.top - obj.height/2 >= c.getHeight())) {
                obj.center();
            }
            obj.setCoords();
            c.renderAll();
            w.layersManager.add(obj);
            w.reloadPrice();
        }
    }
};

/**
 * Object transformation
 */
var TransformCommand = function(canvas, obj, params) {
    var state = {};

    for (var k in params) {
        if (params.hasOwnProperty(k)) state[k] = obj[k];
    }

    var update = function(obj, conf) {
        for (var k in conf) {
            if (!params.hasOwnProperty(k)) {
                continue;
            }
            if (typeof obj['set' + $ucfirst(k)] == 'function') {
                obj['set' + $ucfirst(k)](conf[k]);
            } else {
                obj[k] = conf[k];
            }
        }
    };

    return {
        exec : function() {
            update(obj, params);
            canvas.renderAll();
        },
        unexec : function() {
            update(obj, state);
            canvas.renderAll();
        }
    };
};

/**
 * Object moving
 */
var MovingCommand = function(c, obj, original, current) {
    return {
        exec : function() {
            obj.setLeft(current.left);
            obj.setTop(current.top);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec : function() {
            obj.setLeft(original.left);
            obj.setTop(original.top);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        }
    };
};

/**
 * Rotating object
 */
var RotateCommand = function(c, obj, original, current) {
    return {
        exec : function() {
            obj.setAngle(current.angle);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec : function() {
            obj.setAngle(original.angle);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        }
    }
};

/**
 * Resizing object
 */
var ResizeCommand = function(c, obj, original, current) {
    return {
        exec : function() {
            obj.scaleX = current.scaleX;
            obj.scaleY = current.scaleY;
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec : function() {
            obj.scaleX = original.scaleX;
            obj.scaleY = original.scaleY;
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        }
    }
};

/**
 * Flip command
 */
var FlipCommand = function(c, obj, original, current) {
    return {
        exec : function() {
            obj.flipX = current.flipX;
            obj.flipY = current.flipY;
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec : function() {
            obj.flipX = original.flipX;
            obj.flipY = original.flipY;
            c.setActiveObject(obj);
            c.renderAll();
        }
    }
};

var CurveTextCommand = function(c, obj, original, current){
    return {
        exec: function() {
            obj.setArc(current);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec: function() {
            obj.setArc(original);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        }
    }
};

/**
 * Special command for alignment object by canvas center
 */
var AlignToCenterCommand = function(c, obj) {
    // save original state
    var state = {left : obj.left, top : obj.top};
    return {
        exec : function() {
            obj.center();
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        },
        unexec : function() {
            obj.setLeft(state.left);
            obj.setTop(state.top);
            obj.setCoords();
            c.setActiveObject(obj);
            c.renderAll();
        }
    };
};