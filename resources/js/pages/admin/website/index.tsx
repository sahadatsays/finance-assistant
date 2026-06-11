import { Head, Link } from '@inertiajs/react';
import {
    CreditCard,
    FileText,
    Globe,
    HelpCircle,
    Home,
    Image,
    LayoutTemplate,
    Menu,
    MessageSquareQuote,
    PenLine,
    Search,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const iconMap = {
    home: Home,
    file: FileText,
    menu: Menu,
    layout: LayoutTemplate,
    quote: MessageSquareQuote,
    help: HelpCircle,
    credit: CreditCard,
    pen: PenLine,
    search: Search,
    image: Image,
} as const;

type Module = {
    title: string;
    description: string;
    href: string;
    icon: keyof typeof iconMap;
};

type Stats = Record<string, number>;

export default function WebsiteDashboard({
    stats,
    modules,
}: {
    stats: Stats;
    modules: Module[];
}) {
    return (
        <>
            <Head title="Website Management" />

            <div className="space-y-6">
                <div>
                    <div className="flex items-center gap-2">
                        <Globe className="size-6 text-violet-600" />
                        <h1 className="text-2xl font-semibold">Website Management</h1>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Manage marketing content, navigation, SEO, and homepage sections
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {Object.entries(stats).map(([key, value]) => (
                        <Card key={key} className="border-0 shadow-sm">
                            <CardHeader className="pb-2">
                                <CardDescription className="capitalize">
                                    {key.replace('_', ' ')}
                                </CardDescription>
                                <CardTitle className="text-3xl">{value}</CardTitle>
                            </CardHeader>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {modules.map((module) => {
                        const Icon = iconMap[module.icon];

                        return (
                            <Link key={module.href} href={module.href}>
                                <Card className="h-full border-0 shadow-sm transition hover:border-violet-200 hover:shadow-md">
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div className="flex size-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                                                <Icon className="size-5" />
                                            </div>
                                            <Badge variant="secondary">Manage</Badge>
                                        </div>
                                        <CardTitle className="text-lg">{module.title}</CardTitle>
                                        <CardDescription>{module.description}</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <span className="text-sm font-medium text-violet-600">
                                            Open module →
                                        </span>
                                    </CardContent>
                                </Card>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </>
    );
}
