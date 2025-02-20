import List from "list.js";
import Swal from "sweetalert2";

const Toast = Swal.mixin({
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

let options = {
    valueNames: ['user-first-name', 'user-last-name', 'user-email'],
};

let userList = new List('users', options);

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("a.delete-btn").forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();

            const url = this.href;

            Swal.fire({
                title: "Confirmation",
                text: "Voulez-vous vraiment supprimer cet utilisateur ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "red",
                cancelButtonColor: "var(--lumia-purple)",
                confirmButtonText: "Supprimer",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url).then((response) => {
                        if (response.ok) {
                            Toast.fire({
                                icon: "success",
                                text: response.message
                            })

                            //todo: delete row
                        } else {
                            Toast.fire({
                                icon: "error",
                                text: response.message
                            })
                        }
                    })
                }
            });
        });
    });
})