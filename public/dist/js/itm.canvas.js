var canvasList = [];
var canvasCtxList = [];
var canvasCoordList = [];
var canvasOptions = [];
var canvasBackground = [];

function canvasInit(canvasOptions) {
  this.canvasOptions = canvasOptions;
  for (let i = 0; i < canvasOptions.length; i++) {
    canvasList[i] = document.getElementById("canvas_" + i);
    canvasCtxList[i] = canvasList[i].getContext("2d");
    canvasCoordList[i] = {
      x: 0,
      y: 0,
    };

    // Set Size
    canvasList[i].width = canvasOptions[i].width;
    canvasList[i].height = canvasOptions[i].height;

    canvasBackground[i] = new Image();

    if (typeof canvasOptions[i].background !== "undefined") {
      canvasBackground[i].src = canvasOptions[i].background;

      canvasBackground[i].onload = function () {
        // resize background to fit in the canvas
        var sizer = scalePreserveAspectRatio(
          canvasBackground[i].width,
          canvasBackground[i].height,
          canvasList[i].width,
          canvasList[i].height
        );
        canvasCtxList[i].drawImage(
          canvasBackground[i],
          0,
          0,
          canvasBackground[i].width,
          canvasBackground[i].height,
          0,
          0,
          canvasBackground[i].width * sizer,
          canvasBackground[i].height * sizer
        );
      };
    }

    canvasList[i].addEventListener("mousedown", canvasStart);
    canvasList[i].addEventListener("mouseup", canvasStop);

    canvasList[i].addEventListener("touchstart", canvasStart);
    canvasList[i].addEventListener("touchend", canvasStop);

    canvasList[i].canvasId = i;
  }
}

function scalePreserveAspectRatio(imgW, imgH, maxW, maxH) {
  return Math.min(maxW / imgW, maxH / imgH);
}

function canvasStart(event) {
  event.preventDefault();

  document.addEventListener("mousemove", canvasDraw);
  document.addEventListener("touchmove", canvasDraw);
  document.canvasId = event.currentTarget.canvasId;

  canvasReposition(event, event.currentTarget.canvasId);
}

function canvasStop(event) {
  event.preventDefault();

  const canvasId = event.currentTarget.canvasId;

  document.removeEventListener("mousemove", canvasDraw);
  document.removeEventListener("touchmove", canvasDraw);
  const img = canvasList[canvasId].toDataURL("image/png");
  $("#canvas_image_" + canvasId).val(img);
}

function canvasReposition(event, canvasId) {
  const clientX = event.clientX || event.touches[0].clientX;
  const clientY = event.clientY || event.touches[0].clientY;

  var rect = canvasList[canvasId].getBoundingClientRect();
  const canvasX = clientX - rect.left;
  const canvasY = clientY - rect.top;

  canvasCoordList[canvasId].x = canvasX;
  canvasCoordList[canvasId].y = canvasY;
}

function canvasDraw(event) {
  event.preventDefault();

  var canvasId = event.currentTarget.canvasId;

  canvasCtxList[canvasId].beginPath();
  canvasCtxList[canvasId].lineWidth = 2;
  canvasCtxList[canvasId].lineCap = "round";
  canvasCtxList[canvasId].strokeStyle = "#d63031";
  canvasCtxList[canvasId].moveTo(
    canvasCoordList[canvasId].x,
    canvasCoordList[canvasId].y
  );
  canvasReposition(event, canvasId);
  canvasCtxList[canvasId].lineTo(
    canvasCoordList[canvasId].x,
    canvasCoordList[canvasId].y
  );
  canvasCtxList[canvasId].stroke();
}

function canvasReset(i) {
  // Set Size
  canvasList[i].width = canvasOptions[i].width;
  canvasList[i].height = canvasOptions[i].height;

  var background = new Image();
  background.src = canvasOptions[i].background;

  background.onload = function () {
    // resize background to fit in the canvas
    var sizer = scalePreserveAspectRatio(
      background.width,
      background.height,
      canvasList[i].width,
      canvasList[i].height
    );
    canvasCtxList[i].drawImage(
      background,
      0,
      0,
      background.width,
      background.height,
      0,
      0,
      background.width * sizer,
      background.height * sizer
    );
  };

  const img = canvasList[i].toDataURL("image/png");
  $("#canvas_image_" + i).val(img);
}

function canvasSave(i) {
  canvasOptions[i].callback.call({ result: $("#canvas_image_" + 0).val() });
  _modalTtdHide();
}
