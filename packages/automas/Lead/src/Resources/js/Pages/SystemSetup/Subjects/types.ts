import { PaginatedData, ModalState, AuthContext, CreateProps, EditProps } from '@/types/common';

export interface Subject {
    id: number;
    name: string;
    created_at: string;
}

export interface SubjectFormData {
    name: string;
}

export interface CreateSubjectProps extends CreateProps {
}

export interface EditSubjectProps extends EditProps<Subject> {
}

export type PaginatedSubjects = PaginatedData<Subject>;
export type SubjectModalState = ModalState<Subject>;

export interface SubjectsIndexProps {
    subjects: PaginatedSubjects;
    auth: AuthContext;
    [key: string]: unknown;
}
