document.observe('dom:loaded', function(){
    var canvas = new fabric.Canvas('product_designer_canvas');
    canvas.backgroundColor = '#ffffff';
    canvas.setBackgroundImage('/skin/frontend/base/default/images/gomage/productdesigner/t-shirt_template.png');

    var rect = new fabric.Rect({
        top: 100,
        left: 100,
        width: 60,
        height: 70,
        fill: 'red'
    });

    canvas.add(rect);
});