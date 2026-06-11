import { Link } from '@inertiajs/react';
import { Building2, ChevronDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { switchMethod as switchTenant } from '@/routes/tenant';

type TenantOption = {
    id: number;
    name: string;
    slug: string;
};

type Props = {
    tenant: TenantOption;
    tenants: TenantOption[];
};

export default function TenantSwitcher({ tenant, tenants }: Props) {
    if (tenants.length <= 1) {
        return (
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Building2 className="size-4" />
                <span>{tenant.name}</span>
            </div>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="gap-2">
                    <Building2 className="size-4" />
                    {tenant.name}
                    <ChevronDown className="size-3" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
                {tenants.map((t) => (
                    <DropdownMenuItem key={t.id} asChild>
                        <Link
                            href={switchTenant.url(t.id)}
                            method="post"
                            as="button"
                            className="w-full"
                        >
                            {t.name}
                        </Link>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
