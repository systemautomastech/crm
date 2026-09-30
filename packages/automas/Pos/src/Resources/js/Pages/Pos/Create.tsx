import { Head, router, usePage, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState, useEffect, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ShoppingCart, Search, CreditCard, Plus, Minus, Trash2, X, Home, Printer, FileText, Image, Package, Barcode } from 'lucide-react';
import { getImagePath, formatCurrency,formatDate } from '@/utils/helpers';
import { useFavicon } from '@/hooks/use-favicon';
import { BrandProvider } from '@/contexts/brand-context';
import ReceiptModal from './ReceiptModal';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface WarehouseType {
    id: number;
    name: string;
    address: string;
}

interface Category {
    id: number;
    name: string;
    color: string;
}

interface PosDiscount {
    id: number;
    name: string;
    discount_type: 'percentage' | 'fixed';
    discount_value: number;
    min_quantity: number;
    start_date: string;
    end_date: string;
    category_id: number | null;
    product_ids: number[];
}

interface Product {
    id: number;
    name: string;
    sku: string;
    price: number;
    stock: number;
    category?: string;
    category_id?: number | null;
    image?: string;
    taxes?: Array<{
        id: number;
        name: string;
        rate: number;
    }>;
}

interface CartItem extends Product {
    quantity: number;
    item_discount_value?: number;
    item_discount_amount: number;
}

interface CreateProps {
    customers: Customer[];
    warehouses: WarehouseType[];
    categories: Category[];
    counters: Array<{
        id: number;
        name: string;
        code: string;
        bank_account_id?: number;
    }>;
    discounts: PosDiscount[];
}

// Mirrors backend getDiscountFromTrait::getApplicableDiscount
function getApplicableDiscount(
    discounts: PosDiscount[],
    productId: number,
    quantity: number,
    categoryId: number | null | undefined,
): PosDiscount | null {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const applicable = discounts.filter(d => {
        const start = new Date(d.start_date); start.setHours(0, 0, 0, 0);
        const end   = new Date(d.end_date);   end.setHours(23, 59, 59, 999);
        return start <= today
            && end   >= today
            && d.min_quantity <= quantity
            && (d.product_ids.includes(productId) || d.category_id === (categoryId ?? null));
    });

    // Fixed discounts get priority (100), percentage uses its value — highest wins
    return applicable.sort((a, b) => {
        const pa = a.discount_type === 'percentage' ? a.discount_value : 0;
        const pb = b.discount_type === 'percentage' ? b.discount_value : 0;
        return pb - pa;
    })[0] ?? null;
}

// Mirrors backend getDiscountFromTrait::calculateDiscountAmount * quantity
function computeItemDiscount(
    discount: PosDiscount | null,
    price: number,
    quantity: number,
): { item_discount_value: number; item_discount_amount: number } {
    if (!discount) return { item_discount_value: 0, item_discount_amount: 0 };
    const perUnit = discount.discount_type === 'percentage'
        ? (price * discount.discount_value) / 100
        : discount.discount_value;
    const amount = Math.min(perUnit * quantity, price * quantity);
    return { item_discount_value: discount.discount_value, item_discount_amount: amount };
}

function CreateContent({ customers = [], warehouses = [], categories = [], counters = [], discounts = [] }: CreateProps) {
    const { t } = useTranslation();
    const { adminAllSetting, companyAllSetting, auth } = usePage().props as any;
    useFavicon();

    const isSuperAdmin = auth?.user?.roles?.includes('superadmin');
    const globalSettings = isSuperAdmin ? adminAllSetting : companyAllSetting;

    const [selectedCustomer, setSelectedCustomer] = useState('');
    const [selectedCounter, setSelectedCounter] = useState(() => {
        const saved = sessionStorage.getItem('pos_selected_counter');
        return saved || '';
    });
    const [selectedWarehouse, setSelectedWarehouse] = useState(() => {
        const saved = sessionStorage.getItem('pos_selected_warehouse');
        return saved || (warehouses.length > 0 ? warehouses[0].id.toString() : '');
    });
    const [selectedCategory, setSelectedCategory] = useState<string>('all');
    const [searchTerm, setSearchTerm] = useState('');
    const [skuInput, setSkuInput] = useState('');
    const [products, setProducts] = useState<Product[]>([]);
    const [cart, setCart] = useState<CartItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [imageErrors, setImageErrors] = useState<Record<number, boolean>>({});

    // Compute per-product discount badge data (qty=1) — no API call needed
    const productDiscounts = useMemo(() => {
        const map: Record<number, { item_discount_value: number; item_discount_amount: number; discount_type: string } | null> = {};
        products.forEach(p => {
            const d = getApplicableDiscount(discounts, p.id, 1, p.category_id);
            map[p.id] = d ? { ...computeItemDiscount(d, p.price, 1), discount_type: d.discount_type } : null;
        });
        return map;
    }, [products, discounts]);

    useEffect(() => {
        if (selectedWarehouse) {
            setLoading(true);
            const params = new URLSearchParams({ warehouse_id: selectedWarehouse });
            if (selectedCategory && selectedCategory !== 'all') {
                params.append('category_id', selectedCategory);
            }
            fetch(`${route('pos.products')}?${params}`)
                .then(response => response.json())
                .then(data => setProducts(data))
                .catch(error => console.error('Error:', error))
                .finally(() => setLoading(false));
        }
    }, [selectedWarehouse, selectedCategory]);

    // Clear cart only when warehouse changes
    useEffect(() => {
        setCart([]);
    }, [selectedWarehouse]);

    const handleSkuInput = (value: string) => {
        setSkuInput(value);
        if (value.trim() && selectedWarehouse) {
            const matchedProduct = products.find(product => product.sku === value);
            if (matchedProduct) {
                addToCart(matchedProduct);
                setSkuInput('');
            }
        }
    };

    const addToCart = (product: Product) => {
        setCart(prev => {
            const prevExisting = prev.find(item => item.id === product.id);
            if (prevExisting) {
                const newQty = Math.min(prevExisting.quantity + 1, product.stock);
                const d = getApplicableDiscount(discounts, product.id, newQty, product.category_id);
                return prev.map(item =>
                    item.id === product.id
                        ? { ...item, quantity: newQty, ...computeItemDiscount(d, product.price, newQty) }
                        : item
                );
            }
            const d = getApplicableDiscount(discounts, product.id, 1, product.category_id);
            return [...prev, { ...product, quantity: 1, ...computeItemDiscount(d, product.price, 1) }];
        });
    };

    const updateQuantity = (id: number, quantity: number) => {
        if (quantity <= 0) {
            setCart(prev => prev.filter(item => item.id !== id));
            return;
        }
        setCart(prev => prev.map(item => {
            if (item.id !== id) return item;
            const d = getApplicableDiscount(discounts, id, quantity, item.category_id);
            return { ...item, quantity, ...computeItemDiscount(d, item.price, quantity) };
        }));
    };

    const getSubtotal = () => cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    const getTaxAmount = () => {
        let totalTax = 0;
        cart.forEach(item => {
            const itemSubtotal = item.price * item.quantity;
            const discountedSubtotal = itemSubtotal - (item.item_discount_amount || 0);
            if (item.taxes && item.taxes.length > 0) {
                item.taxes.forEach(tax => {
                    totalTax += (discountedSubtotal * tax.rate) / 100;
                });
            }
        });
        return totalTax;
    };

    const getTotalDiscount = () => {
        return cart.reduce((sum, item) => sum + (item.item_discount_amount || 0), 0);
    };

    const getTaxBreakdown = () => {
        const taxBreakdown: { [key: string]: { name: string; amount: number } } = {};
        cart.forEach(item => {
            const itemSubtotal = item.price * item.quantity;
            const discountedSubtotal = itemSubtotal - (item.item_discount_amount || 0);
            if (item.taxes && item.taxes.length > 0) {
                item.taxes.forEach(tax => {
                    const taxAmount = (discountedSubtotal * tax.rate) / 100;
                    const taxKey = `${tax.name}_${tax.rate}`;
                    if (taxBreakdown[taxKey]) {
                        taxBreakdown[taxKey].amount += taxAmount;
                    } else {
                        taxBreakdown[taxKey] = {
                            name: `${tax.name} (${tax.rate}%)`,
                            amount: taxAmount
                        };
                    }
                });
            }
        });
        return Object.values(taxBreakdown);
    };
    
    const getTotal = () => {
        let total = 0;
        cart.forEach(item => {
            const itemSubtotal = item.price * item.quantity;
            const discountedSubtotal = itemSubtotal - (item.item_discount_amount || 0);
            let itemTax = 0;
            if (item.taxes && item.taxes.length > 0) {
                item.taxes.forEach(tax => {
                    itemTax += (discountedSubtotal * tax.rate) / 100;
                });
            }
            total += discountedSubtotal + itemTax;
        });
        return total;
    };

    const [processing, setProcessing] = useState(false);
    const [showPaymentModal, setShowPaymentModal] = useState(false);
    const [showReceiptModal, setShowReceiptModal] = useState(false);
    const [completedSale, setCompletedSale] = useState<any>(null);
    const [paidAmount, setPaidAmount] = useState('0');
    const [paymentMethod, setPaymentMethod] = useState('cash');
    const [nextPosNumber, setNextPosNumber] = useState('');


    useEffect(() => {
        // Fetch next POS number from backend
        fetch(route('pos.pos-number'))
            .then(response => response.json())
            .then(data => setNextPosNumber(data.pos_number))
            .catch(error => {
                // Fallback to generated number
                const randomCount = Math.floor(Math.random() * 100) + 1;
                setNextPosNumber('#POS' + String(randomCount).padStart(5, '0'));
            });
    }, []);

    const handlePayment = () => {
        // Prevent multiple clicks
        if (processing) return;
        setProcessing(true);


        // Get fresh POS number before processing
        fetch(route('pos.pos-number'))
            .then(response => response.json())
            .then(data => {
                const freshPosNumber = data.pos_number;
                setNextPosNumber(freshPosNumber);

                const selectedCounterData = counters.find(c => c.id.toString() === selectedCounter);
                const formData = {
                    customer_id: selectedCustomer || null,
                    warehouse_id: selectedWarehouse,
                    billing_counter_id: selectedCounter,
                    bank_account_id: selectedCounterData?.bank_account_id || null,
                    items: cart.map(item => ({
                        id: item.id,
                        quantity: item.quantity,
                        price: item.price,
                        item_discount_value: item.item_discount_value || 0,
                        item_discount_amount: item.item_discount_amount || 0,
                    })),
                    discount: 0,
                    tax_amount: getTaxAmount(),
                    payment_method: paymentMethod,
                    paid_amount: parseFloat(paidAmount || '0'),
                    pos_number: freshPosNumber
                };

                setProcessing(true);

                router.post(route('pos.store'), formData, {
                onSuccess: (response: any) => {
                setProcessing(false);
                setCompletedSale({
                    pos_number: response.props?.pos_number || nextPosNumber,
                    items: cart,
                    subtotal: getSubtotal(),
                    tax: getTaxAmount(),
                    discount: 0,
                    total: getTotal(),
                    customer: selectedCustomer ? customers.find(c => c.id.toString() === selectedCustomer) : null,
                    warehouse: warehouses.find(w => w.id.toString() === selectedWarehouse),
                    payment_method: paymentMethod,
                    paid_amount: parseFloat(paidAmount || '0')
                });
                // Close payment modal first, then show receipt
                setShowPaymentModal(false);
                setTimeout(() => {
                    setShowReceiptModal(true);
                }, 100);
            },
            onError: (errors) => {
                setProcessing(false);
                console.error('Payment failed:', errors);
            },
                    preserveState: true,
                    preserveScroll: true
                });
            })
            .catch(error => {
                console.error('Error fetching fresh POS number:', error);
                setProcessing(false);
            });
    };

    const handlePaymentComplete = () => {
        setShowReceiptModal(false);
        setCart([]);
        setSelectedCustomer('');
        setSelectedCounter('');
        setCompletedSale(null);
        // Refresh POS number for next transaction
        fetch(route('pos.pos-number'))
            .then(response => response.json())
            .then(data => setNextPosNumber(data.pos_number))
            .catch(error => console.error('Error fetching new POS number:', error));
    };

    const filteredProducts = products.filter(product =>
        product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        product.sku.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <>
            <Head title={t('POS')} />

            <div className="h-screen bg-gray-50 flex flex-col">
                <div className="flex flex-col lg:flex-row flex-1 overflow-hidden p-2 sm:p-4 gap-2 sm:gap-4 min-h-0">
                    {/* Products Section Card */}
                    <Card className="flex-1 flex flex-col min-w-0 order-2 lg:order-1">
                        <CardContent className="p-3 sm:p-6 flex flex-col h-full overflow-hidden">
                        {/* Controls */}
                        <div className="mb-4 sm:mb-6 space-y-2 sm:space-y-4 flex-shrink-0">
                            <div className="flex flex-col lg:flex-row gap-2 items-stretch lg:items-center">
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <Link href={route('pos.index')}>
                                                <Button variant="outline" className="h-10 px-3 w-full lg:w-auto">
                                                    <Home className="h-4 w-4" />
                                                </Button>
                                            </Link>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{t('Home')}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>

                                <div className="relative flex-1 lg:w-80">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                    <Input
                                        placeholder={t('Search products...')}
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10 h-10"
                                    />
                                </div>

                                <Select value={selectedCustomer} onValueChange={setSelectedCustomer}>
                                    <SelectTrigger className="h-10 w-full lg:w-80">
                                        <SelectValue placeholder={t('Walk-in Customer')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customers.map(customer => (
                                            <SelectItem key={customer.id} value={customer.id.toString()}>
                                                {customer.name} - {customer.email}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Select value={selectedWarehouse} onValueChange={(value) => {
                                    setSelectedWarehouse(value);
                                    sessionStorage.setItem('pos_selected_warehouse', value);
                                }}>
                                    <SelectTrigger className="h-10 w-full lg:w-96">
                                        <SelectValue placeholder={t('Select Warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map(warehouse => (
                                            <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                {warehouse.name} - {warehouse.address}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <div className="relative lg:w-72">
                                                <Barcode className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                                <Input
                                                    placeholder={t('Add To Cart by SKU')}
                                                    className="pl-10 h-10"
                                                    value={skuInput}
                                                    onChange={(e) => handleSkuInput(e.target.value)}
                                                />
                                            </div>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{t('Enter SKU to add product to cart.')}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>

                            <div className="flex flex-wrap gap-2 max-h-[72px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                                <button
                                    onClick={() => setSelectedCategory('all')}
                                    className={`flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-all ${
                                        selectedCategory === 'all'
                                            ? 'bg-primary text-primary-foreground shadow-sm'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                                >
                                    {t('All')}
                                </button>
                                {categories.map(category => (
                                    <button
                                        key={category.id}
                                        onClick={() => setSelectedCategory(category.id.toString())}
                                        className={`flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-all ${
                                            selectedCategory === category.id.toString()
                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        }`}
                                    >
                                        {category.name}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Products Grid */}
                        <div className="flex-1 overflow-y-auto min-h-0">
                        {loading ? (
                            <div className="flex flex-col items-center justify-center py-16 gap-3">
                                <div className="animate-spin rounded-full h-10 w-10 border-2 border-gray-200 border-t-primary"></div>
                                <p className="text-sm text-gray-400">{t('Loading products...')}</p>
                            </div>
                        ) : filteredProducts.length > 0 ? (
                            <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7 gap-2">
                                {filteredProducts.map(product => {
                                    const cartItem = cart.find(i => i.id === product.id);
                                    const disc = productDiscounts[product.id];
                                    const discountedPrice = disc ? product.price - disc.item_discount_amount : null;
                                    const isOutOfStock = product.stock <= 0;

                                    return (
                                        <div
                                            key={product.id}
                                            className={`group relative bg-white rounded-lg border transition-all duration-150 overflow-hidden select-none
                                                ${isOutOfStock
                                                    ? 'border-gray-100 opacity-60 cursor-not-allowed'
                                                    : 'border-gray-100 cursor-pointer hover:border-primary/40 hover:shadow-md active:scale-95'
                                                }`}
                                            onClick={() => !isOutOfStock && addToCart(product)}
                                        >
                                            {/* Image */}
                                            <div className="relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100">
                                                {product.image && !imageErrors[product.id] ? (
                                                    <img
                                                        src={getImagePath(product.image)}
                                                        alt={product.name}
                                                        className="w-full h-full object-cover"
                                                        onError={() => setImageErrors(prev => ({ ...prev, [product.id]: true }))}
                                                    />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center">
                                                        <Image className="w-7 h-7 text-gray-200" />
                                                    </div>
                                                )}

                                                {/* Discount ribbon */}
                                                {disc && (
                                                    <div className="absolute top-0 left-0">
                                                        <div className="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-br-md leading-tight">
                                                            {disc.discount_type === 'percentage'
                                                                ? `${disc.item_discount_value}% OFF`
                                                                : `-${formatCurrency(disc.item_discount_amount)}`}
                                                        </div>
                                                    </div>
                                                )}

                                                {/* In-cart quantity badge */}
                                                {cartItem && (
                                                    <div className="absolute top-1 right-1 bg-primary text-primary-foreground text-[10px] font-bold min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1 shadow-md">
                                                        {cartItem.quantity}
                                                    </div>
                                                )}

                                                {/* Out of stock overlay */}
                                                {isOutOfStock && (
                                                    <div className="absolute inset-0 bg-white/70 flex items-center justify-center">
                                                        <span className="text-[10px] font-semibold text-red-500 bg-white border border-red-200 px-1.5 py-0.5 rounded-full">
                                                            {t('Out of Stock')}
                                                        </span>
                                                    </div>
                                                )}

                                                {/* Add to cart hover button */}
                                                {!isOutOfStock && (
                                                    <div className="absolute bottom-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <div className="bg-primary text-primary-foreground rounded-full w-6 h-6 flex items-center justify-center shadow-md">
                                                            <Plus className="w-3 h-3" />
                                                        </div>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Info */}
                                            <div className="p-1.5">
                                                <p className="font-medium text-gray-900 text-xs truncate leading-snug">{product.name}</p>
                                                <p className="text-[10px] text-gray-400 truncate leading-tight">{product.sku}</p>
                                                <div className="flex items-center justify-between mt-1 gap-1">
                                                    <div className="min-w-0">
                                                        {discountedPrice !== null ? (
                                                            <>
                                                                <p className="text-[10px] text-gray-400 line-through leading-none">{formatCurrency(product.price)}</p>
                                                                <p className="text-xs font-bold text-green-600 leading-tight">{formatCurrency(discountedPrice)}</p>
                                                            </>
                                                        ) : (
                                                            <p className="text-xs font-bold text-green-600 leading-tight">{formatCurrency(product.price)}</p>
                                                        )}
                                                    </div>
                                                    <span className={`flex-shrink-0 text-[10px] font-medium px-1 py-0.5 rounded-full ${
                                                        product.stock > 10
                                                            ? 'bg-green-50 text-green-600'
                                                            : product.stock > 0
                                                                ? 'bg-orange-50 text-orange-500'
                                                                : 'bg-red-50 text-red-500'
                                                    }`}>
                                                        {Math.floor(product.stock)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-16 gap-3">
                                <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                    <Package className="h-8 w-8 text-gray-300" />
                                </div>
                                <p className="text-gray-400 text-sm">{t('No products available')}</p>
                            </div>
                        )}
                        </div>
                        </CardContent>
                    </Card>

                    {/* Cart Sidebar Card */}
                    <Card className="w-full lg:w-80 xl:w-96 flex flex-col flex-shrink-0 min-h-0 order-1 lg:order-2 max-h-[40vh] lg:max-h-none">
                        <CardContent className="p-3 sm:p-4 xl:p-6 border-b flex-shrink-0">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Billing Counter')} <span className="text-red-500">*</span></label>
                                <Select value={selectedCounter} onValueChange={(value) => {
                                    setSelectedCounter(value);
                                    sessionStorage.setItem('pos_selected_counter', value);
                                }} required>
                                    <SelectTrigger className="h-10">
                                        <SelectValue placeholder={t('Select Billing Counter')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {counters.map(counter => (
                                            <SelectItem key={counter.id} value={counter.id.toString()}>
                                                {counter.name} ({counter.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-center justify-between mt-4">
                                <h3 className="text-lg font-bold text-gray-800 flex items-center">
                                    <ShoppingCart className="h-5 w-5 mr-2 text-gray-600" />
                                    {t('Shopping Cart')}
                                </h3>
                                <div className="flex items-center space-x-2">
                                    <Badge variant="secondary">
                                        {cart.length}
                                    </Badge>
                                    {cart.length > 0 && (
                                        <X
                                            className="h-4 w-4 text-red-500 cursor-pointer hover:text-red-700"
                                            onClick={() => setCart([])}
                                        />
                                    )}
                                </div>
                            </div>
                        </CardContent>

                        <CardContent className="flex-1 overflow-auto p-2 sm:p-3 xl:p-4 min-h-0">
                            {cart.length === 0 ? (
                                <div className="text-center py-12">
                                    <div className="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                                        <ShoppingCart className="h-10 w-10 text-gray-400" />
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-600 mb-2">{t('Your cart is empty')}</h3>
                                    <p className="text-sm text-gray-500">{t('Add products to get started')}</p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {cart.map(item => {
                                        const itemTotal = item.price * item.quantity;
                                        const discountAmount = item.item_discount_amount || 0;
                                        const finalAmount = itemTotal - discountAmount;
                                        
                                        return (
                                        <div key={item.id} className="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                                            <div className="flex items-center space-x-3 mb-3">
                                                <div className="w-10 h-10 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                                    {item.image && !imageErrors[item.id] ? (
                                                        <img
                                                            src={getImagePath(item.image)}
                                                            alt={item.name}
                                                            className="w-full h-full object-cover rounded"
                                                            onError={() => setImageErrors(prev => ({ ...prev, [item.id]: true }))}
                                                        />
                                                    ) : (
                                                        <Image className="w-4 h-4 text-gray-400" />
                                                    )}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <h4 className="font-semibold text-gray-900 text-sm truncate">{item.name}</h4>
                                                    <p className="text-sm font-medium text-green-600">{formatCurrency(item.price)} {t('each')}</p>
                                                </div>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => updateQuantity(item.id, 0)}
                                                    className="text-red-500 hover:text-red-700 hover:bg-red-50 p-2"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                            
                                            {/* Price Breakdown - Always show */}
                                            <div className="mb-3 space-y-1 text-xs bg-gray-100 p-2 rounded">
                                                <div className="flex justify-between text-gray-600">
                                                    <span>{t('Price')}:</span>
                                                    <span>{formatCurrency(itemTotal)}</span>
                                                </div>
                                                {discountAmount > 0 && (
                                                    <div className="flex justify-between text-green-600">
                                                        <span>{t('Discount')}:</span>
                                                        <span>-{formatCurrency(discountAmount)}</span>
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center space-x-3">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => updateQuantity(item.id, item.quantity - 1)}
                                                        className="h-7 w-7 p-0 border-gray-300"
                                                    >
                                                        <Minus className="h-3 w-3" />
                                                    </Button>
                                                    <span className="w-8 text-center font-semibold text-sm">{item.quantity}</span>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => updateQuantity(item.id, item.quantity + 1)}
                                                        className="h-7 w-7 p-0 border-gray-300"
                                                        disabled={item.quantity >= item.stock}
                                                    >
                                                        <Plus className="h-3 w-3" />
                                                    </Button>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-lg font-bold text-gray-900">
                                                        {formatCurrency(finalAmount)}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    )})}
                                </div>
                            )}
                        </CardContent>

                        {cart.length > 0 && (
                            <CardContent className="p-2 sm:p-3 xl:p-4 border-t flex-shrink-0">
                                <div className="space-y-2">
                                    <div className="flex justify-between items-center py-0.5">
                                        <span className="text-xs text-gray-600">{t('Subtotal')}</span>
                                        <span className="text-xs text-gray-900">
                                            {formatCurrency(getSubtotal())}
                                        </span>
                                    </div>
                                    {getTotalDiscount() > 0 && (
                                        <div className="flex justify-between items-center py-0.5">
                                            <span className="text-xs text-green-600">{t('Total Discount')}</span>
                                            <span className="text-xs text-green-600">
                                                -{formatCurrency(getTotalDiscount())}
                                            </span>
                                        </div>
                                    )}
                                    {getTaxBreakdown().length > 0 ? (
                                        getTaxBreakdown().map((tax, index) => (
                                            <div key={index} className="flex justify-between items-center py-0.5">
                                                <span className="text-xs text-gray-600">{tax.name}</span>
                                                <span className="text-xs text-gray-900">
                                                    {formatCurrency(tax.amount)}
                                                </span>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="flex justify-between items-center py-0.5">
                                            <span className="text-xs text-gray-600">{t('Tax')}</span>
                                            <span className="text-xs text-gray-900">
                                                {formatCurrency(getTaxAmount())}
                                            </span>
                                        </div>
                                    )}
                                    <div className="flex justify-between items-center py-1 border-t border-gray-200">
                                        <span className="text-lg font-bold text-gray-900">{t('Total')}</span>
                                        <span className="text-xl font-bold text-green-600">
                                            {formatCurrency(getTotal())}
                                        </span>
                                    </div>
                                    <Button
                                        className="w-full h-10 text-sm font-semibold bg-primary hover:bg-primary/90"
                                        onClick={() => {
                                            setPaidAmount(getTotal().toString());
                                            setShowPaymentModal(true);
                                        }}
                                        disabled={cart.length === 0 || !selectedWarehouse || !selectedCounter}
                                    >
                                        <CreditCard className="h-4 w-4 mr-2" />
                                        {t('Checkout')}
                                    </Button>

                                </div>
                            </CardContent>
                        )}
                    </Card>
                </div>
            </div>

            {/* Payment Modal */}
            <Dialog open={showPaymentModal} onOpenChange={(open) => !processing && setShowPaymentModal(open)}>
                <DialogContent className="max-w-[95vw] sm:max-w-2xl max-h-[90vh] overflow-y-auto backdrop-blur-none">
                    <DialogHeader className="pb-4 border-b">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <CreditCard className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <DialogTitle className="text-xl font-semibold">{t('Process Payment')}</DialogTitle>
                            </div>
                        </div>
                    </DialogHeader>

                    <div className="overflow-y-auto flex-1 p-4">
                        {/* Header Info */}
                        <div className="flex flex-col sm:flex-row justify-between gap-4 mb-6">
                            {/* Left Side - POS Details */}
                            <div className="space-y-2 text-sm">
                                <div>
                                    <span className="font-medium">{t('POS Number')}: </span>
                                    <span>{nextPosNumber}</span>
                                </div>
                                <div>
                                    <span className="font-medium">{t('Date')}: </span>
                                    <span>{formatDate(new Date())}</span>
                                </div>
                                <div>
                                    <span className="font-medium">{t('Customer')}: </span>
                                    <span>{selectedCustomer ? customers.find(c => c.id.toString() === selectedCustomer)?.name : t('Walk-in Customer')}</span>
                                </div>
                                <div>
                                    <span className="font-medium">{t('Warehouse')}: </span>
                                    <span>{warehouses.find(w => w.id.toString() === selectedWarehouse)?.name}</span>
                                </div>
                            </div>

                            {/* Right Side - Company Details */}
                            <div className="text-right space-y-1 text-sm">
                                <h2 className="text-lg font-bold">{globalSettings?.company_name || 'Company Name'}</h2>
                                <p>{globalSettings?.company_address || 'Company Address'}</p>
                                <p>{globalSettings?.company_city || 'City'}, {globalSettings?.company_state || 'State'}</p>
                                <p>{globalSettings?.company_country || 'Country'} - {globalSettings?.company_zipcode || 'Zipcode'}</p>
                            </div>
                        </div>

                        {/* Products Table */}
                        <Card className="mb-4">
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full min-w-[600px]">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{t('Product')}</th>
                                            <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{t('Qty')}</th>
                                            <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Price')}</th>
                                            <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Subtotal')}</th>
                                            <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Discount')}</th>
                                            <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{t('Taxes')}</th>
                                            <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Tax Amount')}</th>
                                            <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{t('Total')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {cart.map((item) => {
                                            const itemSubtotal = item.price * item.quantity;
                                            const discountAmount = item.item_discount_amount || 0;
                                            const discountedSubtotal = itemSubtotal - discountAmount;
                                            let itemTaxAmount = 0;
                                            let taxDisplay = '';
                                            if (item.taxes && item.taxes.length > 0) {
                                                const taxNames = item.taxes.map(tax => {
                                                    itemTaxAmount += (discountedSubtotal * tax.rate) / 100;
                                                    return `${tax.name} (${tax.rate}%)`;
                                                });
                                                taxDisplay = taxNames.join(', ');
                                            } else {
                                                taxDisplay = '-';
                                            }
                                            return (
                                                <tr key={item.id}>
                                                    <td className="px-4 py-3">
                                                        <div>
                                                            <p className="text-sm font-medium text-gray-900">{item.name}</p>
                                                            <p className="text-xs text-gray-500">{item.sku}</p>
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-center text-sm">{item.quantity}</td>
                                                    <td className="px-4 py-3 text-right text-sm">{formatCurrency(item.price)}</td>
                                                    <td className="px-4 py-3 text-right text-sm">{formatCurrency(itemSubtotal)}</td>
                                                    <td className="px-4 py-3 text-right text-sm">
                                                        {discountAmount > 0 ? (
                                                            <span className="text-green-600 font-medium">-{formatCurrency(discountAmount)}</span>
                                                        ) : (
                                                            <span className="text-gray-400">-</span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-center text-xs">{taxDisplay}</td>
                                                    <td className="px-4 py-3 text-right text-sm">{formatCurrency(itemTaxAmount)}</td>
                                                    <td className="px-4 py-3 text-right text-sm font-medium">{formatCurrency(discountedSubtotal + itemTaxAmount)}</td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>

                        {/* Totals */}
                        <Card>
                            <CardContent className="p-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span>{t('Subtotal')}:</span>
                                        <span>{formatCurrency(getSubtotal())}</span>
                                    </div>
                                    {getTotalDiscount() > 0 && (
                                        <div className="flex justify-between text-sm">
                                            <span>{t('Discount')}:</span>
                                            <span className="text-red-600">-{formatCurrency(getTotalDiscount())}</span>
                                        </div>
                                    )}
                                    <div className="flex justify-between text-sm">
                                        <span>{t('Tax')}:</span>
                                        <span>{formatCurrency(getTaxAmount())}</span>
                                    </div>
                                    <Separator className="my-2" />
                                    <div className="flex justify-between font-bold text-lg">
                                        <span>{t('Total')}:</span>
                                        <span className="text-green-600">{formatCurrency(getTotal())}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Action Buttons */}
                        <div className="flex justify-end gap-2 mt-6">
                            <Button type="button" variant="outline" onClick={() => setShowPaymentModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button onClick={handlePayment} disabled={processing}>
                                {processing ? t('Processing...') : t('Complete Sale')}
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            <ReceiptModal
                isOpen={showReceiptModal}
                onClose={handlePaymentComplete}
                completedSale={completedSale}
                globalSettings={globalSettings}
            />
        </>
    );
}

export default function Create(props: CreateProps) {
    return (
        <BrandProvider>
            <CreateContent {...props} />
        </BrandProvider>
    );
}
