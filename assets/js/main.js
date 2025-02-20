const { default: Swal } = require('sweetalert2');

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

window.Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let flashDiv = document.getElementById("flash-message")

    if (flashDiv) {
        Toast.fire({
            icon: flashDiv.dataset.icon,
            text: flashDiv.dataset.message
        })
    }
})