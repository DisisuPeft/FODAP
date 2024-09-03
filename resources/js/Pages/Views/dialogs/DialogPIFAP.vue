<script setup>
import {computed, onMounted, ref} from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Loading from "@/Components/Loading.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";

const message = ref("")
const color = ref("")
const timeout = ref()
const snackbar = ref()
const loading = ref(false)

const props = defineProps({
    modelValue: Boolean,
    maxWidth: {
        type: String,
        default: '2xl',
    },
});
const emit = defineEmits([
    'update:modelValue'
]);

const form = ref({
    anio: null,
    periodo: null
});

const fullYears = computed(() => {
    const maxYears = new Date().getFullYear() + 1;
    const minYears = maxYears - 7;
    const years = [];

    for (let i = maxYears; i >= minYears; i--) {
        years.push(i)
    }

    return years
});

const periodos = [
    {id: 1, name: "ENERO-JUNIO"},
    {id: 2, name: "AGOSTO-DICIEMBRE"}
];

function submit(){
    loading.value = true
    axios.get(route('pdf.pifdap'), {
        params: {
            periodo: form.value.periodo,
            anio: form.value.anio,
            tipo_documento: "Programa institucional de formacion docente y actualizacion profesional"
        }
    }).then(res => {

            const url = '/storage/PIFDAP.pdf';
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'PIFDAP.pdf');
            document.body.appendChild(link);
            link.click();
            loading.value = false
            emit('update:modelValue', false)
            notify('¡Atención!', `${res.data[1]}`, `${res.data[0]}`)

    }).catch(error => {
        loading.value = false
        errorMsg('Atención', `${format_errors(error.response?.data?.errors)}`)
        message.value = ""
    })
}


const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
})

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
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="props.modelValue" class="fixed inset-0 overflow-y-auto px-4 py-12 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="props.modelValue" class="fixed inset-0 transform transition-all">
                        <div class="absolute inset-0 bg-gray-500 opacity-75" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="props.modelValue"
                        :class="['mb-6', 'bg-white','rounded-lg', 'overflow-hidden', 'shadow-xl', 'transform', 'transition-all', 'sm:w-full', 'sm:mx-auto', maxWidthClass]"
                    >
                        <div v-if="props.modelValue">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                                <div class="p-4 mt-7 sm:p-8 sm:rounded-lg">
                                    <div class="grid grid-rows-1">
                                        <!--                                        <div class="flex justify-center">-->
                                        <div class="flex justify-start">
                                                                <label for="anio" class="text-md text-gray-500 dark:text-gray-400 bg-white left-1 p-4">Año: </label>
                                        </div>
                                        <div class="flex justify-center p-4">
                                                                    <v-select v-model="form.anio" :items="fullYears" item-title="name" item-value="id" variant="solo"></v-select>
                                        </div>
                                        <!--                                        </div>-->
                                        <div class="flex justify-start">
                                                                <label for="periodo" class="text-md text-gray-500 dark:text-gray-400 bg-white left-1 p-4">Periodo: </label>
                                        </div>
                                        <div class="flex justify-center p-4">
                                                                    <v-select v-model="form.periodo" :items="periodos" item-title="name" item-value="id" variant="solo"></v-select>
                                        </div>
                                    </div>
                                    <div class="grid grid-rows-1 p-3">
                                        <div class="flex justify-end">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="flex justify-end">
                                                    <v-btn @click="emit('update:modelValue', false)" color="red">
                                                        Cerrar
                                                    </v-btn>
                                                </div>
                                                <div  class="flex justify-center">
                                                                                <v-btn @click="submit" color="success" prepend-icon="mdi-file-pdf-box">
                                                                                    Generar
                                                                                </v-btn>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
<!--    <v-dialog width="auto" persistent v-model="props.modelValue">-->
<!--        <form @submit.prevent="submit">-->
<!--            <v-card elevation="3" width="500" height="500">-->
<!--                <v-card-title>Ingresar los datos para generar PDF</v-card-title>-->
<!--                <v-card-text>-->
<!--                    <label for="anio" class="absolute text-md text-gray-500 dark:text-gray-400  bg-white  left-1 pb-4 mb-5 ml-5">Año: </label>-->
<!--                    <div class="pt-5">-->
<!--                        <v-select v-model="form.anio" :items="fullYears" item-title="name" item-value="id" variant="solo"></v-select>-->
<!--                    </div>-->
<!--                    <label for="periodo" class="absolute text-md text-gray-500 dark:text-gray-400  bg-white  left-1 pb-4 mb-5 ml-5">Periodo: </label>-->
<!--                    <div class="pt-5">-->
<!--                        <v-select v-model="form.periodo" :items="periodos" item-title="name" item-value="id" variant="solo"></v-select>-->
<!--                    </div>-->
<!--                </v-card-text>-->
<!--                <v-card-actions>-->
<!--                    <v-row justify="end">-->
<!--                        <v-col cols="4">-->
<!--                            <v-btn color="error" @click="emit('update:modelValue', false)">-->
<!--                                Cancelar-->
<!--                            </v-btn>-->
<!--                        </v-col>-->
<!--                        <v-col cols="4">-->
<!--                            <v-btn @click="submit" color="success" prepend-icon="mdi-file-pdf-box">-->
<!--                                Generar-->
<!--                            </v-btn>-->
<!--                        </v-col>-->
<!--                    </v-row>-->
<!--                </v-card-actions>-->
<!--            </v-card>-->
<!--        </form>-->
<!--    </v-dialog>-->
<!--    <CustomSnackBar-->
<!--    :color="color" :timeout="timeout" :message="message" v-model="snackbar" @update:modelValue="snackbar = $event"-->
<!--    >-->

<!--    </CustomSnackBar>-->
    <Loading v-model="loading" @update:loading="loading = $event">
        <v-fade-transition leave-absolute>
            <v-progress-circular
                v-if="loading"
                color="info"
                :size="64"
                :width="7"
                indeterminate
            ></v-progress-circular>
        </v-fade-transition>
    </Loading>
</template>

<style scoped>

</style>
