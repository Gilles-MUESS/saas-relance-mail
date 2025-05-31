<?php

namespace App\Form;

use App\Entity\SequenceLabel;
use App\Entity\User;
use App\Entity\UserInfo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('isVerified')
            ->add('sequenceLabels', EntityType::class, [
                'class' => SequenceLabel::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('userInfo', EntityType::class, [
                'class' => UserInfo::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
