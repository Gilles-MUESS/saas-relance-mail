<?php

namespace App\Form;

use Dom\Text;
use App\Entity\User;
use App\Entity\UserInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserInfoType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ): void {
		$builder
			->add( 'firstName', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4 col' ],
				'label' => 'Prénom',
			] )
			->add( 'lastName', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4 col' ],
				'label' => 'Nom',
			] )
			->add( 'company', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4' ],
				'label' => 'Entreprise',
				'required' => false,
			] )
			->add( 'address', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4' ],
				'label' => 'Adresse',
				'required' => false,
			] )
			->add( 'address2', TextType::class, [ 
				'mapped' => false,
				'row_attr' => [ 'class' => 'form-outline mb-4' ],
				'label' => '',
				'required' => false,
			] )
			->add( 'cp', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4 col-md-4' ],
				'label' => 'Code postal',
				'required' => false,
			] )
			->add( 'city', TextType::class, [ 
				'row_attr' => [ 'class' => 'form-outline mb-4 col-md-8' ],
				'label' => 'ville',
				'required' => false,
			] )
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [ 
			'data_class' => UserInfo::class,
		] );
	}
}
