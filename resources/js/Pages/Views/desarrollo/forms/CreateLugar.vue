<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import DangerButton from "@/Components/DangerButton.vue";
import NavLink from "@/Components/NavLink.vue";
import Swal from "sweetalert2";
import {AlertLoading, errorMsg, success_alert} from "@/jsfiels/alertas.js";

const message = ref("")
const form = useForm({
    nombreAula: ""
})
const props = defineProps({
    errors: {}
});

const submit = () => {
    // form.post(route('store.lugar'))
    Swal.fire({
        title: '¿Ingreso la información correctas?',
        text: 'Esta acción se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Guardando los datos...', 'Esta accion puede tardar unos minutos')
            form.post(route('store.lugar'), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'El lugar fue creado con exito.')
                    // router.reload()
                },
                onError: () => {
                    // console.log(props.errors)
                    errorMsg('Error', `${format_errors(props.errors)}`)
                    message.value = ""
                },
            });
        }
    })
}

onMounted(() => {

})

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="container mx-auto px-4">
                <div class="grid grid-rows-2">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Aula</h2>
                        </div>
                    </div>
                    <div class="grid grid-cols-1">
                        <NavLink :href="route('parametros.edit')" as="button" type="button">
                            <v-btn icon="mdi-arrow-left">

                            </v-btn>
                        </NavLink>
                    </div>
                </div>
            </div>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <form class="mt-6 space-y-6" @submit.prevent="submit">
                    <v-card height="250" width="auto">
                        <v-container>
                            <v-row justify="center">
                                <v-col cols="12">
                                    <InputLabel for="nombre_aula" value="Nombre del Aula"/>
                                    <v-text-field class="mt-8" v-model="form.nombreAula"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-row class="justify-end">
                                <v-col cols="2">
                                    <NavLink :href="route('parametros.edit')">
                                        <DangerButton>Cancelar</DangerButton>
                                    </NavLink>
                                </v-col>
                                <v-col cols="2">
                                    <PrimaryButton>Guardar</PrimaryButton>
                                </v-col>
                            </v-row>
                        </v-card-actions>
                    </v-card>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
