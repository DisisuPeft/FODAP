<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import NavLink from "@/Components/NavLink.vue";
import {AlertLoading, errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const message = ref();
const form = useForm({
    nombreAula: ""
})
const props = defineProps({
    lugar: Object,
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
            form.put(route('update.lugar', props.lugar.id), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'El lugar fue editado con exito.')
                    // router.reload()
                },
                onError: () => {
                    // console.log(props.errors)
                    notify('Alerta','warning', `${format_errors(props.errors)}`)
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
    if (!props.lugar){
        return form;
    }else{
        form.nombreAula = props.lugar.nombreAula
    }
});
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="container mx-auto px-4">
                <div class="grid grid-rows-2">
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center">
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar aula</h2>
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
                        <v-container>
                            <v-row justify="center">
                                <v-col cols="12">
                                    <InputLabel for="nombre_aula" value="Nombre del Aula"/>
                                    <v-text-field v-model="form.nombreAula"></v-text-field>
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
