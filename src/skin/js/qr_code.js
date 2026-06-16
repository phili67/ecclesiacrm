/* QRCode code */
class QRCodeScanner {
    static listElement = null;
    static stream = null;

    constructor(callback, 
        title = i18next.t("QR Code : Call the register"), 
        prompt = i18next.t("Point your camera at a QR code to check in attendees automatically.")) {
        this.parameters = {};//{fakeVideo:true}
        this.canvas = {};
        this.callback = callback;
        this.title = title;
        this.cameraId = 0;
        this.videoElement = null;
        this.prompt = prompt;

        this.init();

        this.camera = {
            deviceIds: [],
            constraints: {
                'audio': false,
                'video': {
                    'deviceId': this.cameraId,
                    'width': '100%',
                    'height': '100%',                
                }
            }
        };
    }

    setParameters(parameters) {
        this.camera.constraints.video = parameters;
    }

    setTitle(title) {
        this.title = title;
    }

    setPrompt(prompt) {
        this.prompt = prompt;
    }

    BootboxContent = (message) => {
        var frm_str = `<div class="qr-scanner-modal">
            <div class="qr-scanner-header mb-3">
                <h5 class="mb-2"><i class="fas fa-qrcode text-primary"></i> ${i18next.t("Scan a QR Code")}</h5>
                <p class="mb-0 text-muted">${message}</p>
            </div>
            <div class="qr-scanner-stage">
                <div id="loadingMessage" class="qr-scanner-status qr-scanner-status-loading">
                    <span class="qr-scanner-status-icon"><i class="fas fa-camera"></i></span>
                    <span class="qr-scanner-status-text">${i18next.t("Unable to access video stream (please make sure you have a webcam enabled)")}</span>
                </div>
                <canvas id="canvas" class="qr-scanner-canvas" hidden></canvas>
            </div>
            <div class="qr-scanner-footer">
                <div class="form-group">
                    <label for="availableCameras">${i18next.t("Available Cameras")}</label>
                    <select class="form-control" id="availableCameras"></select>
                </div>
            </div>
            <div id="output" class="qr-scanner-feedback" hidden>
                <div id="outputMessage" class="qr-scanner-status qr-scanner-status-idle">
                    <span class="qr-scanner-status-icon"><i class="fas fa-search"></i></span>
                    <span>${i18next.t("No QR code detected yet. Keep the code inside the frame.")}</span>
                </div>
                <div class="qr-scanner-result" hidden>
                    <span class="qr-scanner-result-label">${i18next.t("Detected data")}</span>
                    <span id="outputData" class="qr-scanner-result-value"></span>
                </div>
            </div>
        </div>`;

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    // Fetch an array of devices of a certain type
    async getConnectedDevices(type) {
        if (QRCodeScanner.listElement == null) {
            QRCodeScanner.listElement = $('#availableCameras');
        }
        QRCodeScanner.listElement.empty();

        await navigator.mediaDevices.enumerateDevices()
        .then(devices => {
            for (var i = 0; i < devices.length; i++) {
                if (devices[i].kind !== type) {
                    continue;
                }
                QRCodeScanner.listElement.append($('<option>').val(devices[i].deviceId).text(devices[i].label))
            }
        });
    }

    startVideo(camera) {
        // Grab video element
        this.videoElement = document.getElementById('video');

        if (!this.videoElement) {
            this.videoElement = document.createElement("video");            
        }

        if (this.parameters.fakeVideo) {
            this.videoElement.src = 'http://vjs.zencdn.net/v/oceans.mp4';
            this.videoElement.play();
            return;
        }

        // Stop existing stream if any
        if (QRCodeScanner.stream) {
            QRCodeScanner.stream.getTracks().forEach((track) => {
                track.stop();
            });
            QRCodeScanner.stream = null;
        }

        const self = this;

        navigator.mediaDevices.getUserMedia(camera.constraints)
            .then((mediaStream) => {
                QRCodeScanner.stream = mediaStream;

                // Set the stream to video element
                if ("srcObject" in self.videoElement) {
                    self.videoElement.srcObject = mediaStream;
                } else {
                    self.videoElement.src = window.URL.createObjectURL(mediaStream);
                }
                
                self.videoElement.onloadedmetadata = () => {
                    self.videoElement.play();
                };
            })
            .catch((err) => {
                console.error(err.name + ": " + err.message);
            });
    }

    createCameraChooser() {
        QRCodeScanner.listElement?.on('change', (e) => {
            this.camera.constraints.video.deviceId =  QRCodeScanner.listElement.find(":checked").val();
            this.startVideo(this.camera);
        });
    }

    init() {
         // Listen for changes to media devices and update the list accordingly
        navigator.mediaDevices.addEventListener('devicechange', () => {
            this.getConnectedDevices('videoinput');
        });
        
        this.dialog = bootbox.dialog({
            title: this.title,
            message: this.BootboxContent(this.prompt),
            //size: 'large',
            onShown: (e) => {
                // Load available cameras and start the first one
                this.getConnectedDevices('videoinput').then(() => {
                    this.startVideo(this.camera);
                    this.createCameraChooser();
                });

                if (!this.videoElement) {
                   this.videoElement = document.createElement("video");            
                }
                var canvasElement = document.getElementById("canvas");
                var canvas = canvasElement.getContext("2d");
                var outputContainer = document.getElementById("output");
                var outputMessage = document.getElementById("outputMessage");
                var outputData = document.getElementById("outputData");

                function drawLine(begin, end, color) {
                    canvas.beginPath();
                    canvas.moveTo(begin.x, begin.y);
                    canvas.lineTo(end.x, end.y);
                    canvas.lineWidth = 4;
                    canvas.strokeStyle = color;
                    canvas.stroke();
                }

                var qrcode = '';
                const self = this;

                function tick() {
                    if (self.videoElement.readyState === self.videoElement.HAVE_ENOUGH_DATA) {
                        canvasElement.hidden = false;
                        outputContainer.hidden = false;

                        canvasElement.height = self.videoElement.videoHeight;
                        canvasElement.width = self.videoElement.videoWidth;
                        canvas.drawImage(self.videoElement, 0, 0, canvasElement.width, canvasElement.height);
                        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                        var code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "dontInvert",
                        });
                        if (code) {
                            drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                            drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                            drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                            drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                            outputMessage.hidden = true;
                            outputData.parentElement.hidden = false;
                            outputData.innerText = code.data;

                            if (qrcode != code.data) {
                                qrcode = code.data;
                                
                                if (self.callback)
                                    self.callback(qrcode);
                            }
                        } else {
                            outputMessage.hidden = false;
                            outputData.parentElement.hidden = true;
                        }
                    } else {                        
                        outputMessage.hidden = false;
                        outputData.parentElement.hidden = true;
                    }
                    requestAnimationFrame(tick);
                }
                
                // Start the QR code detection loop
                requestAnimationFrame(tick);
            },
            onHide: function (e) {
                if (QRCodeScanner.stream) {
                    QRCodeScanner.stream.getTracks().forEach((track) => {
                        track.stop();
                    });
                    QRCodeScanner.stream = null;
                }
                QRCodeScanner.listElement?.empty();
                QRCodeScanner.listElement = null;
            },
            buttons: [
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Close"),
                    className: "btn btn-primary",
                    callback: function () {
                        this.modal('hide');
                    }
                }
            ]
        });
    }

    show() {
        this.dialog.modal('show');
        this.createCameraChooser()
    }
}