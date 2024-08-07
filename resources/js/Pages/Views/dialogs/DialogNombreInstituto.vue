<script setup>
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import {onMounted, ref} from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import {errorMsg, success_alert} from "@/jsfiels/alertas.js";

const form = useForm({
    nameInstituto: ""
});
const message = ref("")



const props = defineProps({
    instituto: Array,
    modelValue: Boolean,
    errors: {}
});

const emit = defineEmits([
    'update:modelValue'
]);
const submit = () => {
    if (props.instituto.length === 0){
        return form.post(route('create.instituto'), {
            onSuccess: () => {
                emit('update:modelValue', false)
                success_alert('Exito', 'Exito al crear el nombre del instituto')
            },
            onError: () => {
                emit('update:modelValue', false)
                errorMsg('Alerta', `${format_errors(props.errors)}`)
            }
        })
    }else{
        return form.put(route('update.instituto', props.instituto[0].id), {
            onSuccess: () => {
                emit('update:modelValue', false)
                success_alert('Exito', 'Exito al editar el nombre del instituto')
            },
            onError: () => {
                emit('update:modelValue', false)
                errorMsg('Alerta', `${format_errors(props.errors)}`)
            }
        })
    }
}
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}
onMounted(() => {
    if (props.instituto.length === 0){
        return form.nameInstituto
    }else{
         form.nameInstituto = props.instituto[0].name
    }
})
</script>

<template>
    <v-dialog width="500" v-model="props.modelValue" >
        <v-card>
            <v-card-title>Establecer nombre del Director(a)</v-card-title>
            <v-card-text>
                    <div class="grid grid-cols-1">
                        <div class="flex justify-center mt-4">
                            <v-text-field v-model="form.nameInstituto">

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
    <CustomSnackBar :timeout="timeout" :color="color" :message="message" v-model="snackbarDialog" @update:modelValue="snackbarDialog = $event">

    </CustomSnackBar>
</template>

<style scoped>

</style>
