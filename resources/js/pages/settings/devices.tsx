import { Form, Head } from '@inertiajs/react';
import DeviceController from '@/actions/App/Http/Controllers/Settings/DeviceController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Device = {
    id: number;
    name: string | null;
    platform: string | null;
    browser: string | null;
    ip_address: string | null;
    is_trusted: boolean;
    last_active_at: string | null;
    is_current?: boolean;
};

export default function Devices({ devices }: { devices: Device[] }) {
    return (
        <>
            <Head title="Device management" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Devices"
                    description="Manage devices that have access to your account"
                />

                <div className="space-y-4">
                    {devices.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No devices recorded yet.
                        </p>
                    )}

                    {devices.map((device) => (
                        <div
                            key={device.id}
                            className="flex items-start justify-between gap-4 rounded-lg border p-4"
                        >
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <p className="font-medium">
                                        {device.name ?? 'Unknown device'}
                                    </p>
                                    {device.is_current && (
                                        <Badge variant="secondary">
                                            Current
                                        </Badge>
                                    )}
                                    {device.is_trusted && (
                                        <Badge>Trusted</Badge>
                                    )}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {[device.browser, device.platform]
                                        .filter(Boolean)
                                        .join(' · ') || 'Unknown platform'}
                                </p>
                                {device.ip_address && (
                                    <p className="text-sm text-muted-foreground">
                                        {device.ip_address}
                                    </p>
                                )}
                            </div>

                            {!device.is_current && (
                                <Form
                                    {...DeviceController.destroy.form({
                                        device: device.id,
                                    })}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            size="sm"
                                            disabled={processing}
                                        >
                                            Revoke
                                        </Button>
                                    )}
                                </Form>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}
