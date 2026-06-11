import { Form, Head, Link, router } from '@inertiajs/react';
import CategoryController from '@/actions/App/Http/Controllers/Finance/CategoryController';
import CategoryIcon from '@/components/categories/category-icon';
import CategoryIconPicker from '@/components/categories/category-icon-picker';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { index as categoriesIndex } from '@/routes/categories';
import {
    Archive,
    ArchiveRestore,
    Pencil,
    Plus,
    Shield,
    Tag,
    Trash2,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type Category = {
    id: number;
    name: string;
    type: string;
    color: string;
    icon: string | null;
    kind: string;
    is_system: boolean;
    is_active: boolean;
    archived_at: string | null;
};

type Permissions = {
    view: boolean;
    create: boolean;
    update: boolean;
    delete: boolean;
    archive: boolean;
    restore: boolean;
};

type Props = {
    tenant: { id: number; name: string };
    categories: Category[];
    filters: { archived: boolean };
    permissions: Permissions;
};

type Tab = 'income' | 'expense' | 'archived';

export default function CategoriesIndex({
    tenant,
    categories,
    filters,
    permissions,
}: Props) {
    const [tab, setTab] = useState<Tab>(filters.archived ? 'archived' : 'income');
    const [editing, setEditing] = useState<Category | null>(null);

    const filtered = useMemo(() => {
        if (tab === 'archived') {
            return categories;
        }

        return categories.filter((c) => c.type === tab);
    }, [categories, tab]);

    const systemCount = filtered.filter((c) => c.is_system).length;
    const customCount = filtered.filter((c) => !c.is_system).length;

    return (
        <>
            <Head title="Categories" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Categories</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage income and expense categories for{' '}
                            {tenant.name}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {(['income', 'expense', 'archived'] as Tab[]).map(
                            (t) => (
                                <Button
                                    key={t}
                                    variant={tab === t ? 'brand' : 'outline'}
                                    size="sm"
                                    onClick={() => {
                                        setTab(t);
                                        if (t === 'archived') {
                                            router.get(
                                                categoriesIndex.url({
                                                    query: { archived: 1 },
                                                }),
                                            );
                                        } else {
                                            router.get(categoriesIndex.url());
                                        }
                                    }}
                                >
                                    {t === 'income'
                                        ? 'Income'
                                        : t === 'expense'
                                          ? 'Expense'
                                          : 'Archived'}
                                </Button>
                            ),
                        )}
                    </div>
                </div>

                <div className="flex gap-4 text-sm text-muted-foreground">
                    <span className="flex items-center gap-1">
                        <Shield className="size-4" />
                        {systemCount} system
                    </span>
                    <span className="flex items-center gap-1">
                        <Tag className="size-4" />
                        {customCount} custom
                    </span>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {filtered.map((category) => (
                        <Card
                            key={category.id}
                            className={cn(
                                'border-0 shadow-sm',
                                !category.is_active && 'opacity-70',
                            )}
                        >
                            <CardHeader className="pb-2">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className="flex size-10 items-center justify-center rounded-xl text-white"
                                            style={{
                                                backgroundColor: category.color,
                                            }}
                                        >
                                            <CategoryIcon
                                                icon={category.icon}
                                                className="size-4"
                                            />
                                        </div>
                                        <div>
                                            <CardTitle className="text-base">
                                                {category.name}
                                            </CardTitle>
                                            <CardDescription className="capitalize">
                                                {category.type}
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <Badge
                                        variant="secondary"
                                        className={cn(
                                            category.is_system
                                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300'
                                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                        )}
                                    >
                                        {category.is_system
                                            ? 'System'
                                            : 'Custom'}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="flex flex-wrap gap-2">
                                {permissions.update && category.is_active && (
                                    <Dialog
                                        open={
                                            editing?.id === category.id
                                        }
                                        onOpenChange={(open) =>
                                            !open && setEditing(null)
                                        }
                                    >
                                        <DialogTrigger asChild>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    setEditing(category)
                                                }
                                            >
                                                <Pencil className="mr-1 size-3" />
                                                Edit
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Edit {category.name}
                                                </DialogTitle>
                                            </DialogHeader>
                                            <Form
                                                {...CategoryController.update.form(
                                                    category.id,
                                                )}
                                                className="grid gap-4"
                                                onSuccess={() =>
                                                    setEditing(null)
                                                }
                                            >
                                                {({
                                                    processing,
                                                    errors,
                                                }) => (
                                                    <>
                                                        {!category.is_system && (
                                                            <div className="grid gap-2">
                                                                <Label>
                                                                    Name
                                                                </Label>
                                                                <Input
                                                                    name="name"
                                                                    defaultValue={
                                                                        category.name
                                                                    }
                                                                    required
                                                                />
                                                                {errors.name && (
                                                                    <p className="text-sm text-destructive">
                                                                        {
                                                                            errors.name
                                                                        }
                                                                    </p>
                                                                )}
                                                            </div>
                                                        )}
                                                        <div className="grid gap-2">
                                                            <Label>Color</Label>
                                                            <Input
                                                                name="color"
                                                                type="color"
                                                                defaultValue={
                                                                    category.color
                                                                }
                                                                required
                                                            />
                                                        </div>
                                                        <CategoryIconPicker
                                                            defaultValue={
                                                                category.icon
                                                            }
                                                        />
                                                        <Button
                                                            type="submit"
                                                            variant="brand"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            Save Changes
                                                        </Button>
                                                    </>
                                                )}
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                )}
                                {permissions.archive &&
                                    category.is_active && (
                                        <Link
                                            href={CategoryController.archive.url(
                                                category.id,
                                            )}
                                            method="post"
                                            as="button"
                                            className="inline-flex h-8 items-center rounded-md border px-3 text-sm hover:bg-muted"
                                        >
                                            <Archive className="mr-1 size-3" />
                                            Archive
                                        </Link>
                                    )}
                                {permissions.restore &&
                                    !category.is_active && (
                                        <Link
                                            href={CategoryController.restore.url(
                                                category.id,
                                            )}
                                            method="post"
                                            as="button"
                                            className="inline-flex h-8 items-center rounded-md border px-3 text-sm hover:bg-muted"
                                        >
                                            <ArchiveRestore className="mr-1 size-3" />
                                            Restore
                                        </Link>
                                    )}
                                {permissions.delete &&
                                    !category.is_system &&
                                    category.is_active && (
                                        <Link
                                            href={CategoryController.destroy.url(
                                                category.id,
                                            )}
                                            method="delete"
                                            as="button"
                                            className="inline-flex h-8 items-center rounded-md border border-rose-200 px-3 text-sm text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-400 dark:hover:bg-rose-950"
                                        >
                                            <Trash2 className="mr-1 size-3" />
                                            Delete
                                        </Link>
                                    )}
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {permissions.create && tab !== 'archived' && (
                    <Card className="border-0 shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Plus className="size-5" />
                                Create Custom Category
                            </CardTitle>
                            <CardDescription>
                                Add a new {tab} category for your workspace
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...CategoryController.store.form()}
                                className="grid gap-4 md:grid-cols-2"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="type"
                                            value={tab}
                                        />
                                        <div className="grid gap-2">
                                            <Label>Name</Label>
                                            <Input
                                                name="name"
                                                required
                                                placeholder="Category name"
                                            />
                                            {errors.name && (
                                                <p className="text-sm text-destructive">
                                                    {errors.name}
                                                </p>
                                            )}
                                        </div>
                                        <div className="grid gap-2">
                                            <Label>Type</Label>
                                            <Input
                                                value={tab}
                                                readOnly
                                                className="capitalize bg-muted"
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label>Color</Label>
                                            <Input
                                                name="color"
                                                type="color"
                                                defaultValue="#8b5cf6"
                                                required
                                            />
                                        </div>
                                        <div className="md:col-span-2">
                                            <CategoryIconPicker />
                                        </div>
                                        <div className="md:col-span-2">
                                            <Button
                                                type="submit"
                                                variant="brand"
                                                disabled={processing}
                                            >
                                                Create Category
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}
