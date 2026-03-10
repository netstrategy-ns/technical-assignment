<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef<{ $el?: HTMLInputElement }>('passwordInput');
type Props = {
    deleteUrl?: string;
};

const props = withDefaults(defineProps<Props>(), {
    deleteUrl: '/user/settings/profile',
});

const form = useForm({
    password: '',
});

const submit = (): void => {
    form.delete(props.deleteUrl, {
        preserveScroll: true,
        onError: () => passwordInput.value?.$el?.focus(),
        onSuccess: () => form.reset(),
    });
};

const handleCancel = (): void => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Elimina account"
            description="Elimina il tuo account e tutte le risorse collegate"
        />

        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Attenzione</p>
                <p class="text-sm">
                    Questa azione e definitiva e non puo essere annullata.
                </p>
            </div>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button">
                        Elimina account
                    </Button>
                </DialogTrigger>

                <DialogContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>
                                Vuoi davvero eliminare il tuo account?
                            </DialogTitle>
                            <DialogDescription>
                                Una volta eliminato l'account, anche tutti i
                                dati e le risorse collegate verranno rimossi in
                                modo permanente. Inserisci la password per
                                confermare l'eliminazione definitiva.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">
                                Password
                            </Label>
                            <Input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                name="password"
                                placeholder="Inserisci la password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" @click="handleCancel">
                                    Annulla
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="form.processing"
                                data-test="confirm-delete-user-button"
                            >
                                Elimina account
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
