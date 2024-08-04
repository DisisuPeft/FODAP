<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import NavLink from "@/Components/NavLink.vue";
import {ref} from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import {AlertLoading, errorMsg, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const message = ref("")
const form = useForm({
    departamento_id: null,
    nombre_carrera: "",
    presidente_academia: null
})
const props = defineProps({
    carrera: {
        type: Object
    },
    docente: {
        type: Array
    },
    departamento: {
        type: Array
    },
    errors: {},
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
            form.put(route('update.carrera', props.carrera.id), {
                onSuccess: () => {
                    // form.reset();
                    success_alert('Exito.', 'El aréa académica fue editada con exito.');
                },
                onError: () => {
                    errorMsg('Error.', `${format_errors(props.errors)}}`)
                }
            })
        }
    })
}
onMounted(() => {
    if (!props.carrera){
        return form;
    }else{
        form.nombre_carrera = props.carrera.nameCarrera
        form.departamento_id = props.carrera.departamento_id
        form.presidente_academia = props.carrera.presidente_academia
    }
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Área Academica</h2>
            <NavLink :href="route('parametros.edit')" as="button">
                <v-btn icon="mdi-arrow-left">

                </v-btn>
            </NavLink>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
                <form class="mt-6 space-y-6" @submit.prevent="submit">
                        <v-container>
                            <v-row justify="center">
                                <v-col cols="12">
                                    <InputLabel for="nombre_carrera" value="Nombre de la carrera"/>
                                    <v-text-field v-model="form.nombre_carrera"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <InputLabel for="presidente_academia" value="Presidente de la academia"/>
                                    <v-autocomplete v-model="form.presidente_academia" :items="props.docente" item-title="nombre_completo" item-value="id" variant="solo"></v-autocomplete>
                                </v-col>
                                <v-col cols="12">
                                    <InputLabel for="presidente_academia" value="Departamento al que la carrera pertenece"/>
                                    <v-select v-model="form.departamento_id" :items="props.departamento" item-value="id" item-title="nameDepartamento" variant="solo"></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                        <v-divider></v-divider>
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
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<style scoped>

</style>
