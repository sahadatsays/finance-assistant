import { Form, Head } from '@inertiajs/react';
import SystemSettingsController from '@/actions/App/Http/Controllers/Admin/SystemSettingsController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SettingValue = { value: string | number | boolean; type: string };
type Settings = Record<string, Record<string, SettingValue>>;

export default function AdminSettingsIndex({
    settings,
}: {
    settings: Settings;
}) {
    const general = settings.general ?? {};

    return (
        <>
            <Head title="System Settings" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">System Settings</h1>
                    <p className="text-sm text-muted-foreground">
                        Platform-wide configuration
                    </p>
                </div>

                <Card className="max-w-2xl border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle>General</CardTitle>
                        <CardDescription>
                            Application defaults and feature flags
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...SystemSettingsController.update.form()}
                            className="space-y-4"
                        >
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="app_name">
                                            Application Name
                                        </Label>
                                        <Input
                                            id="app_name"
                                            name="app_name"
                                            defaultValue={String(
                                                general.app_name?.value ?? '',
                                            )}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="support_email">
                                            Support Email
                                        </Label>
                                        <Input
                                            id="support_email"
                                            name="support_email"
                                            type="email"
                                            defaultValue={String(
                                                general.support_email?.value ??
                                                    '',
                                            )}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="trial_days">
                                            Trial Days
                                        </Label>
                                        <Input
                                            id="trial_days"
                                            name="trial_days"
                                            type="number"
                                            min="1"
                                            max="90"
                                            defaultValue={String(
                                                general.trial_days?.value ??
                                                    '14',
                                            )}
                                        />
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="maintenance_mode"
                                            name="maintenance_mode"
                                            defaultChecked={Boolean(
                                                general.maintenance_mode
                                                    ?.value,
                                            )}
                                        />
                                        <Label htmlFor="maintenance_mode">
                                            Maintenance Mode
                                        </Label>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="allow_registration"
                                            name="allow_registration"
                                            defaultChecked={
                                                general.allow_registration
                                                    ?.value !== false
                                            }
                                        />
                                        <Label htmlFor="allow_registration">
                                            Allow Registration
                                        </Label>
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-violet-600 hover:bg-violet-700"
                                    >
                                        Save Settings
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
