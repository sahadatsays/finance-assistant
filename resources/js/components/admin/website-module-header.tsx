import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

type Props = {
    title: string;
    description: string;
};

export default function WebsiteModuleHeader({ title, description }: Props) {
    return (
        <div className="space-y-2">
            <Link
                href="/admin/website"
                className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-violet-600"
            >
                <ArrowLeft className="size-4" />
                Website Management
            </Link>
            <div>
                <h1 className="text-2xl font-semibold">{title}</h1>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
        </div>
    );
}
