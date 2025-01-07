<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import Layout from "@/Layout/App.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

const props = defineProps({
    user: Object,
    roles: Array,
    permissions: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    permissions: props.user.permissions || {},
});

const availablePermissions = computed(() => {
    return Object.keys(props.permissions).reduce((acc, key) => {
        if (props.permissions[key]) {
            acc.push(key);
        }
        return acc;
    }, []);
});

const submit = () => {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("User settings updated successfully");
        },
        onError: () => {
            toast.error("Failed to update user settings");
        },
    });
};

const togglePermission = (permission) => {
    form.permissions[permission] = !form.permissions[permission];
};
</script>

<template>
    <Layout>
        <Card class="w-full mx-auto">
            <CardHeader>
                <CardTitle>User Settings</CardTitle>
                <CardDescription
                    >Manage your account settings and
                    permissions</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Tabs defaultValue="account" class="w-full">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="account">Account</TabsTrigger>
                        <TabsTrigger value="role">Role</TabsTrigger>
                        <TabsTrigger value="permissions"
                            >Permissions</TabsTrigger
                        >
                    </TabsList>
                    <TabsContent value="account">
                        <div class="space-y-4">
                            <div>
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" />
                            </div>
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                />
                            </div>
                        </div>
                    </TabsContent>
                    <TabsContent value="role">
                        <div>
                            <Label for="role">Role</Label>
                            <Select v-model="form.role">
                                <SelectTrigger>
                                    <SelectValue :placeholder="form.role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="role in roles"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{ role }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </TabsContent>
                    <TabsContent value="permissions">
                        <div class="space-y-4">
                            <div
                                v-for="permission in availablePermissions"
                                :key="permission"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="permission"
                                    :checked="form.permissions[permission]"
                                    @update:checked="
                                        togglePermission(permission)
                                    "
                                />
                                <Label :for="permission">{{
                                    permission
                                }}</Label>
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </CardContent>
            <CardFooter>
                <Button @click="submit">Save Changes</Button>
            </CardFooter>
        </Card>
    </Layout>
</template>
