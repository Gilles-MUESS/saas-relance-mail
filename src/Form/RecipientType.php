<?php

namespace App\Form;

use App\Entity\Recipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RecipientType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ): void {
		$builder
			->add( 'firstName', TextType::class, [ 
				'label' => 'Prénom',
				'attr' => [ 
					'placeholder' => 'Prénom',
					'class' => 'form-control',
				],
				'required' => true,
				'constraints' => [ 
					new NotBlank( [ 
						'message' => 'Le prénom est requis.',
					] ),
				],
			] )
			->add( 'lastName', TextType::class, [ 
				'label' => 'Nom',
				'attr' => [ 
					'placeholder' => 'Nom',
					'class' => 'form-control',
				],
				'required' => true,
				'constraints' => [ 
					new NotBlank( [ 
						'message' => 'Le nom est requis.',
					] ),
				],
			] )
			->add( 'email', EmailType::class, [ 
				'label' => 'E-mail',
				'attr' => [ 
					'placeholder' => 'E-mail',
					'class' => 'form-control',
				],
				'required' => true,
				'constraints' => [ 
					new NotBlank( [ 
						'message' => 'L\'adresse e-mail est requise.',
					] ),
				],
			] )
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [ 
			'data_class' => Recipient::class,
		] );
	}
}
