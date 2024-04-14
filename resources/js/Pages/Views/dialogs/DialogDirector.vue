<script setup>
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref} from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";

const form = useForm({
    nameDirector: ""
});
const snackbarD = ref(false);
const message = ref("")
const color = ref()
const timeout = ref(0)
const snackEventActivator = () => {
    snackbarD.value = true;
    message.value = "Parece que los recursos se han actualizado, por favor recarga la pagina"
    color.value = "warning"
    timeout.value = 5000
};
const snackErrorActivator = () => {
    snackbarD.value = true;
    message.value = "No se pudo procesar la solicitud"
    color.value = "error"
    timeout.value = 5000
};
const snackSuccessActivator = () => {
    snackbarD.value = true;
    message.value = "Procesado correctamente"
    color.value = "success"
    timeout.value = 5000
};
const props = defineProps({
    director: Array,
    modelValue: Boolean
});

const emit = defineEmits([
    'update:modelValue'
]);
const submit = () => {
    if (props.director.length === 0){
        return form.post(route('create.director'), {
            onSuccess: () => {
                snackSuccessActivator()
            },
            onError: () => {
                snackErrorActivator()
            }
        })
    }else{
        return form.put(route('update.director', props.director[0].id), {
            onSuccess: () => {
                snackSuccessActivator()
            },
            onError: () => {
                snackErrorActivator()
            }
        })
    }
}

onMounted(() => {
    if (props.director.length === 0){
        return form.nameDirector
    }else{
         form.nameDirector = props.director[0].nameDirector
    }
})
</script>

<template>
    <v-dialog width="auto" v-model="props.modelValue" >
        <v-card>
            <v-card-title>Establecer nombre del Director(a)</v-card-title>
            <v-card-text>
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center mt-4">
                            <v-text-field v-model="form.nameDirector">

                            </v-text-field>
                        </div>
                    </div>
            </v-card-text>
            <v-card-actions>
                <v-row>
                    <v-col align="end">
                        <v-btn color="error" @click="emit('update:modelValue', false)">
                            Cerrar
                        </v-btn>
                    </v-col>
                    <v-col align="center">
                        <v-btn @click="submit" color="success">
                            Guardar
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <CustomSnackBar :timeout="timeout" :color="color" :message="message" v-model="snackbarD" @update:modelValue="snackbarD = $event">

    </CustomSnackBar>
</template>

<style scoped>

</style>
