<script setup>
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import { onMounted, ref } from "vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import { errorMsg, success_alert } from "@/jsfiels/alertas.js";

const form = useForm({
    name: "",
});
const snack = ref(false);
const message = ref("");
const color = ref();
const timeout = ref(0);
const props = defineProps({
    sub: Array,
    modelValue: Boolean,
    errors: {},
});
// console.log(props.sub);
const emit = defineEmits(["update:modelValue"]);
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};
const submit = () => {
    if (props.sub.length === 0) {
        return form.post(route("create.sub"), {
            onSuccess: () => {
                emit("update:modelValue", false);
                success_alert(
                    "Exito",
                    "La subdireccion se ha creado con exito"
                );
            },
            onError: () => {
                errorMsg("Atención", `${format_errors(props.errors)}`);
            },
        });
    } else {
        return form.put(route("update.sub", props.sub[0].id), {
            onSuccess: () => {
                emit("update:modelValue", false);
                success_alert(
                    "Exito",
                    "La subdireccion se ha actualizado con exito"
                );
            },
            onError: () => {
                errorMsg("Atención", `${format_errors(props.errors)}`);
            },
        });
    }
};

onMounted(() => {
    if (props.sub.length === 0) {
        return form.name;
    } else {
        form.name = props.sub[0].name;
    }
});
</script>

<template>
    <v-dialog width="auto" v-model="props.modelValue">
        <v-card>
            <v-card-title>Establecer nombre del subdirector(a)</v-card-title>
            <v-card-text>
                <div class="grid grid-cols-1">
                    <div class="flex justify-center mt-4">
                        <v-text-field v-model="form.name"> </v-text-field>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-row>
                    <v-col align="end">
                        <v-btn
                            color="error"
                            @click="emit('update:modelValue', false)"
                        >
                            Cerrar
                        </v-btn>
                    </v-col>
                    <v-col align="center">
                        <v-btn @click="submit" color="success"> Guardar </v-btn>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <!--    <CustomSnackBar :message="message" :color="color" :timeout="timeout" v-model="snack" @update:modelValue="snack = $event">-->

    <!--    </CustomSnackBar>-->
</template>

<style scoped></style>
