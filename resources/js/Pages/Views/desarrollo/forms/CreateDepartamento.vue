<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="container mx-auto px-4">
                <div class="grid grid-rows-2">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo departamento</h2>
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
<!--            <div class="p-4 mt-7 sm:p-8 bg-white sm:rounded-lg">-->
                <form class="mt-6 space-y-6" @submit.prevent="submit">
                    <v-card height="450" width="auto">
                        <v-container>
                            <v-row justify="center">
                                <v-col cols="12">
                                    <InputLabel for="nombre_departamento" value="Nombre del departamento" class="p-2"/>
                                    <input class="mt-1 block px-3 py-2 bg-white border border-slate-200 rounded-md text-sm shadow-2xl placeholder-slate-400
                                                                                focus:outline-none focus:border-sky-950 focus:ring-1 focus:ring-sky-500 w-full" v-model="form.nombre_departamento">
                                </v-col>
                                <v-col cols="12">
                                    <InputLabel for="jefe_departamento" value="Jefe del departamento" class="p-2"/>
                                    <v-autocomplete v-model="form.jefe_id" :items="props.docente" item-title="nombre_completo" item-value="id" variant="solo"></v-autocomplete>
                                </v-col>
<!--                                <v-col cols="12">-->
<!--                                    <InputLabel for="carrera_adscrita" value="Carrera(s) adscrita(s)" class="p-2"/>-->
<!--                                    <v-select v-model="form.carrera_id" :items="props.carrera" item-value="id" item-title="nameCarrera" variant="solo"></v-select>-->
<!--                                </v-col>-->
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
<!--            </div>-->
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import NavLink from "@/Components/NavLink.vue";
import {AlertLoading, errorMsg, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const message = ref("")

const form = useForm({
    carrera_id: null,
    nombre_departamento: "",
    jefe_id: null,
})
const props = defineProps({
    carrera: {
        type: Array
    },
    docente: {
        type: Array
    },
    departamento: {
        type: Array
    },
    errors: {}
});


const submit = () => {
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
            form.post(route('store.departamento'), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Recurso creado', 'El departamento fue creado con exito.')
                },
                onError: () => {
                    errorMsg('Error', `${format_errors(props.errors)}`)
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
    return message.value.split('.').join('. ');
}
onMounted(() => {

})
</script>

<style scoped>

</style>
