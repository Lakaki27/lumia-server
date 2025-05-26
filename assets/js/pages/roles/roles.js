import List from "list.js";
import Swal from "sweetalert2";

let options = {
    valueNames: ['role-name', 'role-slug'],
};

let rolesList = new List('roles', options);

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("a.delete-btn").forEach(button => {
        button.addEventListener('click', async function (e) {
            e.preventDefault();

            const url = this.href;

            Swal.fire({
                title: "Confirmation",
                text: "Voulez-vous vraiment supprimer ce rôle ?",
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
                                text: "Rôle supprimé. Veuillez recharger la page pour voir les modifications." 
                            })

                            //todo: delete row
                        } else {
                            Toast.fire({
                                icon: "error",
                                text: "Erreur dans la suppression du rôle." 
                            })
                        }
                    })
                }
            });
        });
    });
})