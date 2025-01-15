<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdateProfileInformationFormSelected from "@/Pages/Views/desarrollo/forms/UpdateProfileInformationFormSelected.vue";
import UpdatePasswordFormSelected from "@/Pages/Views/desarrollo/forms/UpdatePasswordFormSelected.vue";
import NavLink from "@/Components/NavLink.vue";
import { ref } from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Swal from "sweetalert2";
import {
    AlertLoading,
    errorMsg,
    notify,
    success_alert,
} from "@/jsfiels/alertas.js";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import UpdateProfileEmailForm from "@/Pages/Profile/Partials/UpdateProfileEmailForm.vue";

const props = defineProps({
    user: Object,
    docente: Array,
    departamento: Array,
    rol: Array,
    errors: {},
});
const message = ref("");

const submit = (form) => {
    Swal.fire({
        title: "¿Ingreso la información correcta?",
        text: "Esta acción se puede revertir",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        icon: "info",
        timerProgressBar: true,
    }).then((res) => {
        if (res.isConfirmed) {
            AlertLoading(
                "Guardando los datos...",
                "Esta accion puede tardar unos minutos"
            );
            form.patch(route("update.user", props.user.id), {
                onSuccess: () => {
                    form.reset();
                    success_alert("Exito", "El usuario fue editado con exito.");
                    // router.reload()
                },
                onError: () => {
                    // console.log(props.errors)
                    notify(
                        "Alerta",
                        "warning",
                        `${format_errors(props.errors)}`
                    );
                    message.value = "";
                },
            });
        }
    });
};

const submit_passwordform = (form) => {
    Swal.fire({
        title: "¿Ingreso la información correcta?",
        text: "Esta acción se puede revertir",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        icon: "info",
        timerProgressBar: true,
    }).then((res) => {
        if (res.isConfirmed) {
            form.put(route("update.password", ["admin", props.user.id]), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    success_alert("Exito", "Contraseña actualizada con exito.");
                },
                onError: () => {
                    // if (form.errors.password) {
                    //     form.reset('password', 'password_confirmation');
                    //     // passwordInput.value.focus();
                    // }
                    // if (form.errors.current_password) {
                    //     form.reset('current_password');
                    //     // currentPasswordInput.value.focus();
                    // }
                    errorMsg("Atención", `${format_errors(props.errors)}`);
                    message.value = "";
                },
            });
        }
    });
};

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <NavLink :href="route('parametros.edit')" as="button">
                <v-btn icon="mdi-arrow-left" color="blue-darken-1"> </v-btn>
            </NavLink>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 mt-2">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdateProfileInformationFormSelected
                    :rol="props.rol"
                    :departamento="props.departamento"
                    :docente="props.docente"
                    :user="props.user"
                    @form:update-docente="submit"
                >
                </UpdateProfileInformationFormSelected>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 mt-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdateProfileEmailForm
                    :user="props.user"
                    :errors="props.errors"
                    :from="`config`"
                ></UpdateProfileEmailForm>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 mt-10">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdatePasswordFormSelected
                    :user="props.user"
                    @form:password="submit_passwordform"
                ></UpdatePasswordFormSelected>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
