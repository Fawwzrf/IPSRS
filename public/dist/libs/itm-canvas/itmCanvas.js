var jqxhr = { abort: function () {} };
(function ($) {
  "use strict";
  $.fn.itmCanvas = function (options, callback) {
    var settings = $.extend(
      {
        width: "300",
        height: "300",
        strokeStyle: "#192a56",
        afterDrawAction: null,
        imageResultBase64: null,
      },
      options
    );

    // callback
    var itmCanvas = {
      getImageResultBase64: function () {
        return getImageResultBase64();
      },
      setBackground: function (val, imgWidth, imgHeight) {
        setBackground(val, imgWidth, imgHeight);
      },
      setStrokeStyle: function(val){
        setStrokeStyle(val);
      },
    };

    // initial
    var imageResultBase64 = "";
    var canvasId = $(this).attr("id");
    var canvasObj = document.getElementById(canvasId);
    var canvasCtx = canvasObj.getContext("2d");
    var canvasCoord = {
      x: 0,
      y: 0,
    };

    var canvasImageId = "canvas_image_" + canvasId;
    $(
      '<input type="hidden" name="canvas_image_' +
        canvasId +
        '" id="' +
        canvasImageId +
        '" value="">'
    ).insertAfter(this);

    canvasObj.height = settings.height;
    canvasObj.width = settings.width;

    var canvasBackground = new Image();
    if (typeof settings.background !== undefined) {
      canvasBackground.src = settings.background;

      canvasBackground.onload = function () {
        // resize background to fit in the canvas
        var sizer = scalePreserveAspectRatio(
          canvasBackground.width,
          canvasBackground.height,
          settings.width,
          settings.height
        );
        canvasCtx.drawImage(
          canvasBackground,
          0,
          0,
          canvasBackground.width,
          canvasBackground.height,
          0,
          0,
          canvasBackground.width * sizer,
          canvasBackground.height * sizer
        );
      };
    }

    canvasObj.addEventListener("mousedown", canvasStart);
    canvasObj.addEventListener("mouseup", canvasStop);

    canvasObj.addEventListener("touchstart", canvasStart);
    canvasObj.addEventListener("touchend", canvasStop);

    function canvasStart(event) {
      event.preventDefault();

      document.addEventListener("mousemove", canvasDraw);
      document.addEventListener("touchmove", canvasDraw);

      canvasReposition(event);
    }

    function canvasStop(event) {
      event.preventDefault();

      document.removeEventListener("mousemove", canvasDraw);
      document.removeEventListener("touchmove", canvasDraw);
      const img = canvasObj.toDataURL("image/png");

      imageResultBase64 = img;
      $("#" + canvasImageId).val(img);

      if ($.isFunction(settings.afterDrawAction)) {
        settings.afterDrawAction.call();
      }
    }

    function scalePreserveAspectRatio(imgW, imgH, maxW, maxH) {
      return Math.min(maxW / imgW, maxH / imgH);
    }

    function canvasReposition(event) {
      const clientX = event.clientX || event.touches[0].clientX;
      const clientY = event.clientY || event.touches[0].clientY;

      var rect = canvasObj.getBoundingClientRect();
      const canvasX = clientX - rect.left;
      const canvasY = clientY - rect.top;

      canvasCoord.x = canvasX;
      canvasCoord.y = canvasY;
    }

    function canvasDraw(event) {
      event.preventDefault();

      canvasCtx.beginPath();
      canvasCtx.lineWidth = 4;
      canvasCtx.lineCap = "round";
      canvasCtx.strokeStyle = settings.strokeStyle;
      canvasCtx.moveTo(canvasCoord.x, canvasCoord.y);
      canvasReposition(event, canvasId);
      canvasCtx.lineTo(canvasCoord.x, canvasCoord.y);
      canvasCtx.stroke();
    }

    // outside function
    function setStrokeStyle(color){
      settings.strokeStyle = color;
    }

    function getImageResultBase64() {
      return imageResultBase64;
    }

    function setBackground(val, imgWidth, imgHeight) {
      canvasBackground.src = val;

      canvasObj.width = imgWidth;
      canvasObj.height = imgHeight;

      canvasBackground.onload = function () {
        // resize background to fit in the canvas
        var sizer = scalePreserveAspectRatio(
          canvasBackground.width,
          canvasBackground.height,
          imgWidth,
          imgHeight
        );
        canvasCtx.drawImage(
          canvasBackground,
          0,
          0,
          canvasBackground.width,
          canvasBackground.height,
          0,
          0,
          canvasBackground.width * sizer,
          canvasBackground.height * sizer
        );
      };
    }

    return itmCanvas;
  };
})(jQuery);
