<template>
    <v-dialog
        v-model="props.modelValue"
        transition="dialog-bottom-transition">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 mt-7 sm:p-8 bg-white shadow sm:rounded-lg">
<!--                <form class="mt-6 space-y-6" @submit.prevent="submit">-->
                    <v-container>
                        <v-row justify="center">
                            <v-col cols="12">
                                <InputLabel for="nombre_carrera" value="Nombre de la carrera"/>
                                <input class="mt-1 block px-3 py-2 bg-white border border-slate-200 rounded-md text-sm shadow-2xl placeholder-slate-400
                                    focus:outline-none focus:border-sky-950 focus:ring-1 focus:ring-sky-500 w-full" v-model="form.nameCarrera">
<!--                                <v-text-field v-model="form.nameCarrera"></v-text-field>-->
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
<!--                            <NavLink :href="route('parametros.edit')">-->
                                <button class="rounded-md bg-red-500 hover:bg-red-700 p-4 text-white" @click="close">Cancelar</button>
<!--                            </NavLink>-->
                        </v-col>
                        <v-col cols="2">
                            <v-btn type="submit" color="blue-darken-1" icon="mdi-content-save-all" size="x-large" class="floating-btn" @click="submit">

                            </v-btn>

                            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Guardado.</p>
                            </Transition>
                        </v-col>
                    </v-row>
<!--                </form>-->
            </div>
        </div>
    </v-dialog>
</template>

<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref, watch} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import NavLink from "@/Components/NavLink.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";


const form = useForm({
    departamento_id: null,
    nameCarrera: "",
    presidente_academia: null
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
    modelValue: Boolean
});

const dialogValue = ref(props.modelValue);
const emit = defineEmits([
    'update:modelValue',
    'form:carrera'
])
const submit = () => {
    emit('form:carrera', form)
}

const close = () => {
    emit('update:modelValue', false)
    // close()
}

onMounted(() => {

})

watch(() => props.modelValue, (newVal) => {
    dialogValue.value = newVal;
    // console.log(dialogValue.value)
});
</script>

<style scoped>

</style>
