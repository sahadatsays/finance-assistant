import { Form, Head } from '@inertiajs/react';
import SessionController from '@/actions/App/Http/Controllers/Settings/SessionController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Session = {
    id: string;
    ip_address: string | null;
    user_agent: string | null;
    last_activity: number;
    is_current: boolean;
};

export default function Sessions({ sessions }: { sessions: Session[] }) {
    return (
        <>
            <Head title="Session management" />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Heading
                        variant="small"
                        title="Sessions"
                        description="Review and revoke active sessions on your account"
                    />

                    {sessions.length > 1 && (
                        <Form {...SessionController.destroyOthers.form()}>
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                    disabled={processing}
                                >
                                    Revoke other sessions
                                </Button>
                            )}
                        </Form>
                    )}
                </div>

                <div className="space-y-4">
                    {sessions.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No active sessions found.
                        </p>
                    )}

                    {sessions.map((session) => (
                        <div
                            key={session.id}
                            className="flex items-start justify-between gap-4 rounded-lg border p-4"
                        >
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <p className="font-medium">
                                        {session.user_agent ?? 'Unknown browser'}
                                    </p>
                                    {session.is_current && (
                                        <Badge variant="secondary">
                                            Current
                                        </Badge>
                                    )}
                                </div>
                                {session.ip_address && (
                                    <p className="text-sm text-muted-foreground">
                                        {session.ip_address}
                                    </p>
                                )}
                            </div>

                            {!session.is_current && (
                                <Form
                                    {...SessionController.destroy.form({
                                        session: session.id,
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
