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
import Swal from "sweetalert2";

const props = defineProps({
    user: Object,
    roles: Array,
    allPermissions: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    permissions: props.user.permissions || {},
});

const availablePermissions = computed(() => {
    return Object.keys(props.allPermissions);
});

const isPermissionEnabled = computed(() => (permission) => {
    if (form.role === "admin") {
        return true;
    }
    if (form.role === "manager") {
        return ![
            "products_delete",
            "pos_delete",
            "po_delete",
            "purchasing_delete",
            "vouchers_delete",
            "members_delete",
            "products_edit",
            "pos_edit",
            "po_edit",
            "purchasing_edit",
            "vouchers_edit",
            "members_edit",
            "categories_edit",
            "categories_delete",
            "units_edit",
            "units_delete",
            "suppliers_edit",
            "suppliers_delete",
            "kelompok_account_edit",
            "kelompok_account_delete",
            "users_edit",
            "users_delete",
        ].includes(permission);
    }
    if (form.role === "kasir") {
        return ["pos_view"].includes(permission);
    }
    return false;
});

const submit = () => {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: "Yeay!",
                text: "Your work has been saved",
                icon: "success",
                showConfirmButton: false,
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        onError: () => {
            Swal.fire({
                title: "Oops!",
                text: "Something went wrong",
                icon: "error",
            });
        },
    });
};

const togglePermission = (permission) => {
    if (isPermissionEnabled.value(permission)) {
        form.permissions[permission] = !form.permissions[permission];
    }
};

const updateRole = (newRole) => {
    form.role = newRole;
    // Reset permissions based on the new role
    form.permissions = {};
    availablePermissions.value.forEach((permission) => {
        if (isPermissionEnabled.value(permission)) {
            form.permissions[permission] = true;
        }
    });
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
                            <Select
                                v-model="form.role"
                                @update:modelValue="updateRole"
                            >
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
                        <div class="grid grid-cols-4 gap-4 space-y-2">
                            <div
                                v-for="permission in availablePermissions"
                                :key="permission"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="permission"
                                    :checked="form.permissions[permission]"
                                    :disabled="!isPermissionEnabled(permission)"
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
