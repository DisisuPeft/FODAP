<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head, usePage} from '@inertiajs/vue3';
import DocenteInfo from "@/Pages/Profile/Partials/DocenteInfo.vue";
import {computed, onMounted, ref} from "vue";
import UpdatePasswordFormSelected from "@/Pages/Views/desarrollo/forms/UpdatePasswordFormSelected.vue";
import Swal from "sweetalert2";
import {errorMsg, success_alert} from "@/jsfiels/alertas.js";

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    departamento: {
        type: Array
    },
    docente: {
        type: Object
    },
    tipo_plaza: {
        type: Array,
    },
    puesto: {
        type: Array
    },
    carrera: {
        type: Array,
    },
    auth: {
        type: Object
    },
    posgrado: {
        type: Array
    },
    permiso_to_edit: {
        type: Boolean
    }
});
const user = computed(() => usePage().props.auth.user);
const message = ref("");
// console.log(props.permiso_to_edit)
onMounted(() => {
    window.Echo.private(`App.Models.User.${props.auth.user.id}`).notification((notification) => {
        switch (notification.type){
            case 'App\\Notifications\\NewDeteccionNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\DeteccionEditadaNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\AceptadoNotification':
                props.auth.usernotifications++
                break;
            case 'App\\Notifications\\ObservacionNotification':
                props.auth.usernotifications++
                break;
        }
    })
});
const submit_passwordform = (form) => {
    Swal.fire({
        title: '¿Ingreso la información correcta?',
        text: 'Esta acción se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            form.put(route('update.password', ["user", user.value.id]), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset()
                    success_alert('Exito', 'Debes iniciar sesión nuevamente con tu nueva contraseña.')
                },
                onError: () => {
                    if (form.errors.password) {
                        form.reset('password', 'password_confirmation');
                        // passwordInput.value.focus();
                    }
                    if (form.errors.current_password) {
                        form.reset('current_password');
                        // currentPasswordInput.value.focus();
                    }
                    errorMsg('Atención', `${format_errors(props.errors)}`)
                    message.value = ""
                },
            });
        }
    })
}

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ')
}
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perfil</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <template v-if="user.role === 1 || permiso_to_edit === true">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>
                </template>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <DocenteInfo :docente="docente" :carrera="carrera" :departamento="departamento"
                    :puesto="puesto" :tipo_plaza="tipo_plaza" :posgrado="posgrado" class="max-w-xl" :auth="props.auth.user"
                    />
                </div>
                <template v-if="user.role === 1 || permiso_to_edit === true">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdatePasswordFormSelected :user="props.user" @form:password="submit_passwordform"></UpdatePasswordFormSelected>
                    </div>
                </template>

                <template v-if="user.role === 1">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
