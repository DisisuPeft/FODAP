<script setup>

import PrimaryButton from "@/Components/PrimaryButton.vue";
import NavLink from "@/Components/NavLink.vue";
import {ref} from "vue";
import Swal from "sweetalert2";
import {AlertLoading, notify, success_alert} from "@/jsfiels/alertas.js";
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    lugar: {
        type: Array
    }
});

const form = useForm({
    nombreAula: ""
})
const search = ref("")

const header = [
    {key: "id", title: "ID"},
    {key: "nombreAula", title: "Nombre"},
    // {key: "disponible", title: "Disponibilidad"},
    {key: "options", title: "Opciones"},
    {key: 'delete', title: "Eliminar"}
];


const eliminar = (id) => {
    Swal.fire({
        title: '¿Esta seguro que desea eliminar esta sala/aula?',
        text: 'Esta acción no se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            AlertLoading('Eliminando los datos...', 'Esta accion puede tardar unos minutos')
            form.delete(route('delete.lugar', id), {
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'El lugar fue eliminado con exito.')
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
</script>

<template>
    <v-data-table
        :items="props.lugar"
        :headers="header"
        fixed-header
        next-icon="mdi-arrow-right-bold-circle"
        prev-icon="mdi-arrow-left-bold-circle"
        items-per-page="5"
        items-per-page-text="Paginas"
    >

        <template v-slot:no-data>
            <v-alert :value="true" color="warning">
                <v-icon left color="white">warning</v-icon
                >No se encontraron datos
            </v-alert>
        </template>

        <template v-slot:item.options="{item}">
            <NavLink  :href="route('edit.lugar', item.id)" as="button" type="button">
                <v-btn class="" icon color="success">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
            </NavLink>
        </template>
        <template v-slot:item.delete="{item}">
<!--            <NavLink  :href="route('delete.lugar', item.id)" as="button" type="button">-->
                <v-btn class="" icon color="error" @click="eliminar(item.id)">
                    <v-icon>mdi-delete-forever</v-icon>
                </v-btn>
<!--            </NavLink>-->
        </template>
    </v-data-table>
</template>


<style scoped>

</style>
