<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import NavLink from "@/Components/NavLink.vue";
import {computed, ref} from "vue";
import DeleteUserSelectForm from "@/Pages/Views/desarrollo/forms/DeleteUserSelectForm.vue";
import {router} from "@inertiajs/vue3";
import {errorMsg, success_alert} from "@/jsfiels/alertas.js";

const message = ref("")

const props = defineProps({
    users: {
        type: Array
    },
    search: {
        type: String
    },
    errors: {}
});


const header = [
    {key: "email", title: "Correo Institucional"},
    {key: "departamento.nameDepartamento", title: "Departamento a su cargo"},
    {key: "edit", title: "Editar Usuario"},
    {key: "delete", title: "Eliminar usuario"},
    {key: "permiso", title: "Permitir actualizar email u contraseña"},
    {key: "revocar", title: "Revocar permiso"},
];


const usersA = computed(() => {
    return props.users.filter(user => {
        return user.role === 3
    })
});
const wich_user = (user) => {
    return user;
}
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}
const submit = (item) => {
    router.post(route('permiso.edit', item), {}, {
        // headers: {
        //     'Custom-Header': 'value',
        // },
        onSuccess: () => {
            success_alert('Exito', 'Se le otorgo el permiso al usuario.')
        },
        onError: () => {
            errorMsg('Exito', `${format_errors(props.errors)}`)
            message.value = ""
        }
    })
}

const submitRevoke = (item) => {
    router.post(route('permiso.revoke', item), {}, {
        // headers: {
        //     'Custom-Header': 'value',
        // },
        onSuccess: () => {
            success_alert('Exito', 'Se le revoco el permiso al usuario.')
        },
        onError: () => {
            errorMsg('Atención', `${format_errors(props.errors)}`)
            message.value = ""
        }
    })
}

</script>

<template>

    <v-data-table
        :items="usersA"
        :headers="header"
        fixed-header
        next-icon="mdi-arrow-right-bold-circle"
        prev-icon="mdi-arrow-left-bold-circle"
        items-per-page="5"
        items-per-page-text="Paginas"
        :search="props.search"
    >

        <template v-slot:no-data>
            <v-alert :value="true" color="warning">
                <v-icon left color="white">warning</v-icon
                >No se encontraron datos
            </v-alert>
        </template>

<!--        <template v-slot:item.options="{item}">-->
<!--            <NavLink  :href="route('edit.lugar', item.id)">-->
<!--                <PrimaryButton class="bg-blue-accent-4">-->
<!--                    <v-icon>mdi-pencil</v-icon>-->
<!--                </PrimaryButton>-->
<!--            </NavLink>-->
<!--        </template>-->
        <template v-slot:item.edit="{item}">
            <NavLink :href="route('edit.user', item.id)" as="button">
                <v-btn icon color="success"><v-icon>mdi-pencil</v-icon></v-btn>
            </NavLink>
        </template>
        <template v-slot:item.delete="{item}">
            <DeleteUserSelectForm :user="wich_user(item)" :errors="props.errors"></DeleteUserSelectForm>
        </template>
        <template v-slot:item.permiso="{item}">
<!--            <NavLink :href="route('permiso.edit', item.id)" as="button" method="post">-->
                <v-btn height="35" color="info" @click="submit(item.id)">
                    <v-icon>mdi-check</v-icon>
                </v-btn>
<!--            </NavLink>-->
        </template>
        <template v-slot:item.revocar="{item}">
            <div v-if="item.permissions.length > 0">
                <td class="text-center">
<!--                    <NavLink :href="route('permiso.revoke', item.id)" as="button" method="post">-->
                        <v-btn width="250" height="35" color="error" @click="submitRevoke(item.id)">
                            <v-icon>mdi-cancel</v-icon>
                        </v-btn>
<!--                    </NavLink>-->
                </td>
            </div>
        </template>

    </v-data-table>
</template>


<style scoped>

</style>
