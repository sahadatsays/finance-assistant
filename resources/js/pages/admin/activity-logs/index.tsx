import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Log = {
    id: number;
    log_name: string;
    description: string;
    causer: string;
    tenant: string | null;
    ip_address: string | null;
    created_at: string;
};

export default function AdminActivityLogsIndex({
    logs,
    meta,
}: {
    logs: { data: Log[] };
    meta: { total: number };
}) {
    return (
        <>
            <Head title="Activity Logs" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Activity Logs</h1>
                    <p className="text-sm text-muted-foreground">
                        {meta.total} platform events recorded
                    </p>
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>
                            Tenant lifecycle, plan changes, and settings updates
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {logs.data.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    No activity recorded yet.
                                </p>
                            )}
                            {logs.data.map((log) => (
                                <div
                                    key={log.id}
                                    className="flex items-start justify-between gap-4 rounded-lg border p-4"
                                >
                                    <div className="space-y-1">
                                        <p className="text-sm font-medium">
                                            {log.description}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {log.causer}
                                            {log.tenant && ` · ${log.tenant}`}
                                            {log.ip_address &&
                                                ` · ${log.ip_address}`}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <Badge variant="outline">
                                            {log.log_name}
                                        </Badge>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {new Date(
                                                log.created_at,
                                            ).toLocaleString()}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
