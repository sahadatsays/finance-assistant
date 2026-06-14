import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    title: string;
    description: string;
    action?: ReactNode;
};

export default function WebsiteModuleHeader({ title, description, action }: Props) {
    return (
        <div className="space-y-2">
            <Link
                href="/admin/website"
                className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-violet-600"
            >
                <ArrowLeft className="size-4" />
                Website Management
            </Link>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold">{title}</h1>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </div>
                {action}
            </div>
        </div>
    );
}
