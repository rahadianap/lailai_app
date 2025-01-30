<script setup>
import { Button } from "../../components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
import { Input } from "../../components/ui/input";
import { Label } from "../../components/ui/label";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    password: "",
});

const errors = ref({});

const submit = () => {
    form.post("/login", {
        onError: (error) => {
            errors.value = error;
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-center w-screen h-screen mx-auto">
        <Card class="w-full max-w-sm">
            <CardHeader>
                <CardTitle class="text-2xl"> Login </CardTitle>
                <CardDescription>
                    Enter your email below to login to your account.
                </CardDescription>
            </CardHeader>
            <form @submit.prevent="submit">
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Username</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            :class="{ 'is-invalid': errors.name }"
                            type="text"
                            required
                            autocomplete="false"
                            autofocus
                        />
                        <span class="text-red-500" v-if="errors.name">{{
                            errors.name
                        }}</span>
                    </div>
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            :class="{ 'is-invalid': errors.password }"
                            type="password"
                            required
                        />
                        <span class="text-red-500" v-if="errors.password">{{
                            errors.password
                        }}</span>
                    </div>
                </CardContent>
                <CardFooter>
                    <Button class="w-full" :disabled="form.processing">
                        Sign in
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>
