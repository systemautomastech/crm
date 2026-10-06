import { Link, usePage } from "@inertiajs/react";
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
} from "@/components/ui/sidebar";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from "@/components/ui/dropdown-menu";
import { ChevronDown } from "lucide-react";
import { NavItem } from "@/types";
import { useEffect, useMemo, useState } from "react";

interface NavMainProps {
    items?: NavItem[];
    searchQuery?: string;
}

export function NavMain({ items = [], searchQuery = "" }: NavMainProps) {
    const page = usePage();

    const currentPath = useMemo(() => {
        return page.url.split("?")[0].replace(/\/+$/, "") || "/";
    }, [page.url]);

    /**
     * Get pathname from a navigation href.
     */
    const getPath = (href?: string): string => {
        if (!href) return "";

        try {
            return (
                new URL(href, "http://localhost").pathname
                    .split("?")[0]
                    .replace(/\/+$/, "") || "/"
            );
        } catch {
            return href.split("?")[0].replace(/\/+$/, "") || "/";
        }
    };

    /**
     * Extract all valid menu paths to find best/most specific route match
     */
    const allMenuPaths = useMemo(() => {
        const paths: string[] = [];
        const extract = (list: NavItem[]) => {
            list.forEach((it) => {
                const p = getPath(it.href);
                if (p && p !== "/") paths.push(p);
                if (it.activeRoutes) {
                    it.activeRoutes.forEach((routePattern) => {
                        const rp = getPath(routePattern);
                        if (rp && rp !== "/") paths.push(rp);
                    });
                }
                if (it.children) extract(it.children);
            });
        };
        extract(items);
        return paths;
    }, [items]);

    /**
     * Longest prefix / exact match path for current URL
     */
    const bestMatchingPath = useMemo(() => {
        const matches = allMenuPaths.filter(
            (p) => currentPath === p || currentPath.startsWith(`${p}/`),
        );
        return matches.sort((a, b) => b.length - a.length)[0] || "";
    }, [allMenuPaths, currentPath]);

    /**
     * Check whether a navigation path is active.
     */
    const isUrlActive = (itemPath: string): boolean => {
        if (!itemPath) return false;

        if (itemPath === "/") {
            return currentPath === "/";
        }

        if (currentPath === itemPath) return true;

        return bestMatchingPath === itemPath;
    };

    /**
     * Recursively check whether an item or any of its descendants
     * matches the current route.
     */
    const isItemActive = (item: NavItem): boolean => {
        const ownPath = getPath(item.href);

        const isOwnActive = !!ownPath && isUrlActive(ownPath);

        const isExtraRouteActive =
            !!item.activeRoutes &&
            item.activeRoutes.some((routePattern) => {
                const currentRouteName =
                    typeof route === "function" && (route as any)().current
                        ? (route as any)().current()
                        : "";
                if (currentRouteName) {
                    if (routePattern.endsWith("*")) {
                        const prefix = routePattern.slice(0, -1);
                        if (currentRouteName.startsWith(prefix)) return true;
                    } else if (currentRouteName === routePattern) {
                        return true;
                    }
                }
                const routePath = getPath(routePattern);
                return isUrlActive(routePath);
            });

        const hasActiveChild =
            !!item.children &&
            item.children.some((child) => isItemActive(child));

        return isOwnActive || isExtraRouteActive || hasActiveChild;
    };

    /**
     * Filter navigation items while preserving parents when one
     * of their children matches the search query.
     */
    const filterItems = (navItems: NavItem[], query: string): NavItem[] => {
        const normalizedQuery = query.trim().toLowerCase();

        if (!normalizedQuery) {
            return navItems;
        }

        return navItems.reduce<NavItem[]>((result, item) => {
            const matchesTitle = item.title
                .toLowerCase()
                .includes(normalizedQuery);

            const filteredChildren = item.children
                ? filterItems(item.children, normalizedQuery)
                : [];

            if (matchesTitle || filteredChildren.length > 0) {
                result.push({
                    ...item,
                    children:
                        filteredChildren.length > 0
                            ? filteredChildren
                            : item.children,
                });
            }

            return result;
        }, []);
    };

    const filteredItems = useMemo(
        () => filterItems(items, searchQuery),
        [items, searchQuery],
    );

    return (
        <SidebarGroup>
            <SidebarMenu>
                {filteredItems.map((item, index) => (
                    <NavMenuItem
                        key={item.id ?? `${item.title}-${index}`}
                        item={item}
                        isItemActive={isItemActive}
                        getPath={getPath}
                    />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

/**
 * Main recursive navigation item.
 */
function NavMenuItem({
    item,
    isItemActive,
    getPath,
}: {
    item: NavItem;
    isItemActive: (item: NavItem) => boolean;
    getPath: (href?: string) => string;
}) {
    const hasChildren = !!item.children && item.children.length > 0;

    const active = isItemActive(item);

    /**
     * Parent menu with children.
     */
    if (hasChildren) {
        return (
            <SidebarMenuItem>
                {/* Expanded sidebar */}
                <ExpandedNavItem
                    item={item}
                    active={active}
                    isItemActive={isItemActive}
                    getPath={getPath}
                />

                {/* Collapsed sidebar */}
                <CollapsedNavItem
                    item={item}
                    active={active}
                    isItemActive={isItemActive}
                />
            </SidebarMenuItem>
        );
    }

    /**
     * Simple leaf item.
     */
    return (
        <SidebarMenuItem>
            <SidebarMenuButton asChild isActive={active} tooltip={item.title}>
                <Link href={item.href!}>
                    {item.icon && <item.icon />}
                    <span>{item.title}</span>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    );
}

/**
 * Expanded sidebar navigation.
 */
function ExpandedNavItem({
    item,
    active,
    isItemActive,
    getPath,
}: {
    item: NavItem;
    active: boolean;
    isItemActive: (item: NavItem) => boolean;
    getPath: (href?: string) => string;
}) {
    const [open, setOpen] = useState(active);

    useEffect(() => {
        if (active) {
            setOpen(true);
        }
    }, [active]);

    return (
        <Collapsible
            open={open}
            onOpenChange={setOpen}
            className="group/collapsible group-data-[collapsible=icon]:hidden"
        >
            <CollapsibleTrigger asChild>
                <SidebarMenuButton tooltip={item.title} isActive={active}>
                    {item.icon && <item.icon />}
                    <span>{item.title}</span>

                    <ChevronDown className="ml-auto h-4 w-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-180" />
                </SidebarMenuButton>
            </CollapsibleTrigger>

            <CollapsibleContent>
                <SidebarMenuSub>
                    {item.children?.map((child, index) => (
                        <ExpandedChildItem
                            key={child.id ?? `${child.title}-${index}`}
                            item={child}
                            isItemActive={isItemActive}
                            getPath={getPath}
                        />
                    ))}
                </SidebarMenuSub>
            </CollapsibleContent>
        </Collapsible>
    );
}

/**
 * Recursive expanded child item.
 */
function ExpandedChildItem({
    item,
    isItemActive,
    getPath,
}: {
    item: NavItem;
    isItemActive: (item: NavItem) => boolean;
    getPath: (href?: string) => string;
}) {
    const hasChildren = !!item.children && item.children.length > 0;

    const active = isItemActive(item);

    if (hasChildren) {
        return (
            <SidebarMenuSubItem>
                <ExpandedNestedItem
                    item={item}
                    active={active}
                    isItemActive={isItemActive}
                    getPath={getPath}
                />
            </SidebarMenuSubItem>
        );
    }

    const itemPath = getPath(item.href);
    const isActive = !!itemPath && isItemActive(item);

    return (
        <SidebarMenuSubItem>
            <SidebarMenuSubButton asChild isActive={isActive}>
                <Link href={item.href!}>
                    {item.icon && <item.icon className="h-4 w-4" />}

                    <span>{item.title}</span>
                </Link>
            </SidebarMenuSubButton>
        </SidebarMenuSubItem>
    );
}

/**
 * Expanded nested navigation item.
 */
function ExpandedNestedItem({
    item,
    active,
    isItemActive,
    getPath,
}: {
    item: NavItem;
    active: boolean;
    isItemActive: (item: NavItem) => boolean;
    getPath: (href?: string) => string;
}) {
    const [open, setOpen] = useState(active);

    useEffect(() => {
        if (active) {
            setOpen(true);
        }
    }, [active]);

    return (
        <Collapsible
            open={open}
            onOpenChange={setOpen}
            className="group/subcollapsible"
        >
            <CollapsibleTrigger asChild>
                <SidebarMenuSubButton isActive={active}>
                    {item.icon && <item.icon className="h-4 w-4" />}

                    <span>{item.title}</span>

                    <ChevronDown className="ml-auto h-3 w-3 transition-transform duration-200 group-data-[state=open]/subcollapsible:rotate-180" />
                </SidebarMenuSubButton>
            </CollapsibleTrigger>

            <CollapsibleContent>
                <SidebarMenuSub>
                    {item.children?.map((child, index) => (
                        <ExpandedNestedChild
                            key={child.id ?? `${child.title}-${index}`}
                            item={child}
                            isItemActive={isItemActive}
                            getPath={getPath}
                        />
                    ))}
                </SidebarMenuSub>
            </CollapsibleContent>
        </Collapsible>
    );
}

/**
 * Nested child inside expanded sidebar.
 */
function ExpandedNestedChild({
    item,
    isItemActive,
    getPath,
}: {
    item: NavItem;
    isItemActive: (item: NavItem) => boolean;
    getPath: (href?: string) => string;
}) {
    const hasChildren = !!item.children && item.children.length > 0;

    if (hasChildren) {
        return (
            <SidebarMenuSubItem>
                <ExpandedNestedItem
                    item={item}
                    active={isItemActive(item)}
                    isItemActive={isItemActive}
                    getPath={getPath}
                />
            </SidebarMenuSubItem>
        );
    }

    const active = isItemActive(item);

    return (
        <SidebarMenuSubItem>
            <SidebarMenuSubButton asChild isActive={active} className="text-sm">
                <Link href={item.href!}>
                    {item.icon && <item.icon className="h-3 w-3" />}

                    <span>{item.title}</span>
                </Link>
            </SidebarMenuSubButton>
        </SidebarMenuSubItem>
    );
}

/**
 * Collapsed sidebar navigation.
 *
 * Uses Radix DropdownMenuSub instead of nesting independent
 * DropdownMenu components.
 */
function CollapsedNavItem({
    item,
    active,
    isItemActive,
}: {
    item: NavItem;
    active: boolean;
    isItemActive: (item: NavItem) => boolean;
}) {
    return (
        <div className="hidden group-data-[collapsible=icon]:block">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <SidebarMenuButton tooltip={item.title} isActive={active}>
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    side="right"
                    align="start"
                    className="w-48"
                >
                    {item.children?.map((child, index) => (
                        <CollapsedDropdownItem
                            key={child.id ?? `${child.title}-${index}`}
                            item={child}
                            isItemActive={isItemActive}
                        />
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

/**
 * Collapsed dropdown item.
 */
function CollapsedDropdownItem({
    item,
    isItemActive,
}: {
    item: NavItem;
    isItemActive: (item: NavItem) => boolean;
}) {
    const hasChildren = !!item.children && item.children.length > 0;

    const active = isItemActive(item);

    if (hasChildren) {
        return (
            <DropdownMenuSub>
                <DropdownMenuSubTrigger
                    className="gap-2"
                    data-active={active || undefined}
                >
                    {item.icon && <item.icon className="h-4 w-4" />}

                    <span>{item.title}</span>
                </DropdownMenuSubTrigger>

                <DropdownMenuSubContent className="w-44">
                    {item.children?.map((child, index) => (
                        <CollapsedNestedDropdownItem
                            key={child.id ?? `${child.title}-${index}`}
                            item={child}
                            isItemActive={isItemActive}
                        />
                    ))}
                </DropdownMenuSubContent>
            </DropdownMenuSub>
        );
    }

    return (
        <DropdownMenuItem asChild data-active={active || undefined}>
            <Link href={item.href!} className="flex items-center gap-2">
                {item.icon && <item.icon className="h-4 w-4" />}

                <span>{item.title}</span>
            </Link>
        </DropdownMenuItem>
    );
}

/**
 * Recursive collapsed dropdown item.
 */
function CollapsedNestedDropdownItem({
    item,
    isItemActive,
}: {
    item: NavItem;
    isItemActive: (item: NavItem) => boolean;
}) {
    const hasChildren = !!item.children && item.children.length > 0;

    const active = isItemActive(item);

    if (hasChildren) {
        return (
            <DropdownMenuSub>
                <DropdownMenuSubTrigger
                    className="gap-2"
                    data-active={active || undefined}
                >
                    {item.icon && <item.icon className="h-4 w-4" />}

                    <span>{item.title}</span>
                </DropdownMenuSubTrigger>

                <DropdownMenuSubContent className="w-44">
                    {item.children?.map((child, index) => (
                        <CollapsedNestedDropdownItem
                            key={child.id ?? `${child.title}-${index}`}
                            item={child}
                            isItemActive={isItemActive}
                        />
                    ))}
                </DropdownMenuSubContent>
            </DropdownMenuSub>
        );
    }

    return (
        <DropdownMenuItem asChild data-active={active || undefined}>
            <Link href={item.href!} className="flex items-center gap-2">
                {item.icon && <item.icon className="h-3 w-3" />}

                <span className="text-sm">{item.title}</span>
            </Link>
        </DropdownMenuItem>
    );
}
