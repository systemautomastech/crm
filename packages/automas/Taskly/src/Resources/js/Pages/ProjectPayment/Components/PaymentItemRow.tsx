import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Trash2 } from 'lucide-react';
import { formatCurrency } from '@/utils/helpers';

interface Milestone {
    id: number;
    title: string;
    cost: number;
}

interface PaymentItemRowProps {
    index: number;
    item: {
        milestone_id: string;
        price: string;
        discount_percentage: string;
        discount_amount: number;
        total_amount: number;
    };
    milestones: Milestone[];
    loadingMilestones: boolean;
    projectSelected: boolean;
    errors: Record<string, string>;
    onChange: (index: number, field: string, value: string) => void;
    onRemove: (index: number) => void;
    canRemove: boolean;
}

export default function PaymentItemRow({
    index,
    item,
    milestones,
    loadingMilestones,
    projectSelected,
    errors,
    onChange,
    onRemove,
    canRemove,
}: PaymentItemRowProps) {
    const { t } = useTranslation();

    return (
        <tr>
            <td className="px-4 py-4">
                <Select 
                    value={item.milestone_id} 
                    onValueChange={(value) => onChange(index, 'milestone_id', value)}
                    disabled={!projectSelected || loadingMilestones}
                >
                    <SelectTrigger className="w-full">
                        <SelectValue placeholder={
                            loadingMilestones ? t('Loading...') : t('Select Milestone')
                        } />
                    </SelectTrigger>
                    <SelectContent>
                        {milestones.map((milestone) => (
                            <SelectItem key={milestone.id} value={milestone.id.toString()}>
                                {milestone.title} ({formatCurrency(milestone.cost)})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors[`items.${index}.milestone_id`]} />
            </td>
            <td className="px-4 py-4">
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    value={item.price}
                    onChange={(e) => onChange(index, 'price', e.target.value)}
                    className="w-24 text-sm"
                    required
                />
                <InputError message={errors[`items.${index}.price`]} />
            </td>
            <td className="px-4 py-4">
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    value={item.discount_percentage}
                    onChange={(e) => onChange(index, 'discount_percentage', e.target.value)}
                    className="w-20 text-sm"
                />
            </td>
            <td className="px-4 py-4">
                <span className="text-sm font-medium">
                    {formatCurrency(item.total_amount)}
                </span>
            </td>
            <td className="px-4 py-4 text-center">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => onRemove(index)}
                    disabled={!canRemove}
                    className="text-red-600 hover:text-red-800 h-8 w-8 p-0"
                >
                    <Trash2 className="h-4 w-4" />
                </Button>
            </td>
        </tr>
    );
}
