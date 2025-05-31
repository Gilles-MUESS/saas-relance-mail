<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class MessageType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ): void {
		$builder
			->add( 'sendAt', DateType::class, [ 
				'row_attr' => [ 
					'class' => 'form-outline mb-4',
				],
				'widget' => 'single_text',
				'input' => 'datetime_immutable',
				'label' => 'Date de l\'envoi',
				'placeholder' => 'Date de l\'envoi',
				'attr' => [ 
					'min' => ( new \DateTime() )->format( 'Y-m-d' ),
				],
			] )
			->add( 'sendAtTime', TimeType::class, [ 
				'row_attr' => [ 
					'class' => 'form-outline mb-4',
				],
				'widget' => 'choice',
				'input' => 'datetime_immutable',
				'label' => 'Heure de l\'envoi',
				'placeholder' => [ 
					'hour' => 'Heure', 'minute' => null
				],
				'minutes' => $this->getMinutes(),
				'with_seconds' => false,
			] )
			->add( 'subject', TextType::class, [ 
				'row_attr' => [ 
					'class' => 'form-outline mb-4',
				],
				'label' => 'Objet',
				'attr' => [ 
					'placeholder' => 'Objet',
					'class' => 'form-control',
				],
			] )
			->add( 'message', TextareaType::class, [ 
				'row_attr' => [ 
					'class' => 'form-outline mb-4',
				],
				'label' => 'Message',
				'attr' => [ 
					'placeholder' => 'Message',
					'class' => 'form-control',
					'rows' => 5,
				],
			] )
			->add( 'attachment', FileType::class, [ 
				'row_attr' => [ 
					'class' => 'form-outline mb-4',
				],
				'label' => 'Pièce jointe',
				'attr' => [ 
					'placeholder' => 'Pièce jointe',
					'class' => 'form-control',
				],
				'required' => false,
				'multiple' => true,
			] )
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [ 
			'data_class' => Message::class,
		] );
	}

	private function getMinutes(): array {
		$minutes = [];
		for ( $i = 0; $i < 60; $i += 5 ) {
			$minutes[ str_pad( $i, 2, '0', STR_PAD_LEFT ) ] = str_pad( $i, 2, '0', STR_PAD_LEFT );
		}
		return $minutes;
	}
}
